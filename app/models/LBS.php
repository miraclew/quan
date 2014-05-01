<?php
/**
 * 位置服务类
 */
define('ZERO_VALUE', 0.00000001);//浮点0
class LBS {
    const EARTH_RADIUS = 6371; // km
    const KM_PER_DEGREE = 111.12; //km
    const MAX_LIMIT = 1000;

    const GOOGLE_API_GEOCODE_URL = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=%s,%s&sensor=false&language=zh-CN';
    /**
     * 反向地理位置编码 (经纬度转地理位置)
     */
    public function revGeoCode($lng, $lat) {
        $url = sprintf(self::GOOGLE_API_GEOCODE_URL, $lat,$lng);
        $req = new HttpRequest();
        $resp = $req->get($url, $header=array(), $timeout=10);
        if ($resp === false) {
            throw new ErrRtnException(Err::$FAIL);
        }
        $data = json_decode($resp, true);

        $locality = '';
        if(strtolower($data['status']) == 'ok') {
            $ac = $data['results'][0]['address_components'];
            $locality = $this->extractAddressComponents($ac, 'locality');
            $locality .= ' '.$this->extractAddressComponents($ac, 'sublocality');
        }
        return $locality;
    }

    private function extractAddressComponents($addressComponents, $component) {
        foreach ($addressComponents as $value) {
            if($value['types'][0] == $component)
                return $value['long_name'];
        }
        return '';
    }

    /**
     * 计算距离 (KM)
     */
    public function calcDistance($lng1, $lat1, $lng2, $lat2) {
        $radLat1 = $this->rad($lat1);
        $radLat2 = $this->rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = $this->rad($lng1) - $this->rad($lng2);
        $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
        $s = $s *self::EARTH_RADIUS;
        $s = round($s * 10000) / 10000;
        return $s;
    }

    // 求弧度
    private function rad($d) {
        return $d * 3.1415926535898 / 180.0;
    }

    public static function validatLatlng($lng, $lat) {
        return floatval($lat) < 90 && floatval($lat) > -90 &&  floatval($lng) < 180 && floatval($lng) > -180;
    }

    /**
    * latitude: 纬度
    * longitude: 经度
    */
    public static function setCircleLocation($circle_id, $lng, $lat) {
        if (!self::validatLatlng($lng, $lat)) return;

        $m = new MongoClient(MONGO_DB);
        $db = $m->pois;
        $circles = $db->circles;

        $data['circle_id'] = intval($circle_id);
        $data['loc'] = array('type'=>'Point','coordinates'=>array(floatval($lng), floatval($lat)));
        $data['created'] = new MongoDate(time());
        $ret = $circles->save($data);
        return $ret;
    }

    // $maxDistance 公里
    public function getCirclesAround($lng, $lat, $skip, $limit, $maxDistance=0) {
        if (!LBS::validatLatlng($lng, $lat)) return array();

        $m = new MongoClient(MONGO_DB);
        $db = $m->pois;
        $circles = $db->circles;

        $condition = array('loc' => array('$nearSphere' => array('$geometry' => array(
            'type' => 'Point',
            'coordinates'=>array(floatval($lng), floatval($lat))))));
        if($maxDistance != 0) {
            $max_distance = floatval($maxDistance);
            $condition['loc']['$maxDistance'] = $max_distance; // distance in meters
        }

        $cursor = $circles->find($condition)->skip($skip)->limit($limit);

        $data = array();
        foreach ($cursor as $value) {
            $distance = self::calcDistance($lng, $lat, $value['loc']['coordinates'][0], $value['loc']['coordinates'][1]);
            $value['distance'] = $distance;
            $value['distance_text'] = $this->formatDistance($distance);

            $data[] = $value;
        }

        //return $this->sortByDistance($data);
        return $data;
    }

    // sort by distance ourself to walk around mongo's bug (partial)
    private function sortByDistance($items) {
        function cmp($a, $b)
        {
            if (!isset($a['distance']) || !isset($a['distance'])) {
                return 0;
            }

            if ($a['distance'] > $b['distance']) {
                return 1;
            }
            elseif ($a['distance'] > $b['distance']) {
                return -1;
            }
            else {
                return 0;
            }
        }

        usort($items, "cmp");
        return $items;
    }

    public static function setUserLocation($accountId, $lng, $lat, $location) {
        $m = new MongoClient(MONGO_DB);
        $db = $m->pois;
        $pois = $db->users;

        $data['_id'] = intval($accountId);
        if($location == '') {
            return $pois->remove($data);
        }
        else {
            $data['loc'] = array(floatval($lng), floatval($lat));
            $lastlogin = UserCounter::getInfo($accountId)->lastlogintime;
            $gender = UserCounter::getBasicInfo($accountId)->gender;
            $data['lastlogin'] = new MongoDate($lastlogin);
            $data['gender'] = $gender;

            return $pois->save($data);
        }
    }

    public static function updateLastlogintime($accountId) {
        $m = new MongoClient(MONGO_DB);
        $db = $m->pois;
        $pois = $db->users;

        $data['_id'] = intval($accountId);
        $data['lastlogin'] = time();
        return $pois->save($data);
    }

    public function getUsersAroundMe($accountId, $page=1, $pageLimit=200, $maxDistance=0, $gender=-1) {
        $info = UserCounter::getInfo($accountId);
        $lat = floatval($info->lat);
        $lng = floatval($info->lng);
        $accountId = intval($accountId);

        if (!LBS::validatLatlng($lng, $lat)) return array();

        // $con = new MongoClient("mongodb://{$username}:{$password}@{$host}");
        $m = new MongoClient(MONGO_DB);
        $db = $m->pois;
        $pois = $db->users;

        $condition = array('loc' => array('$near' => array(floatval($lng), floatval($lat))), '_id' => array('$ne' => $accountId));
        if($maxDistance != 0) {
            $max_distance = floatval($maxDistance);
            $condition['loc']['$maxDistance'] = $max_distance/self::KM_PER_DEGREE;
        }

        $before = new MongoDate(time() - 7*86400);
        $condition['lastlogin'] = array('$gt' => $before);
        if ($gender != -1) {
            $condition['gender'] = $gender;
        }

        $skip = ($page-1)*$pageLimit;
        if($skip<0) $skip = 0;

        $cursor = $pois->find($condition)->skip($skip)->limit($pageLimit);

        $data = iterator_to_array($cursor, false);
        if(empty($data)) {
            $data = array();
        }

        foreach ($data as &$value) {
            $distance = self::calcDistance($lng, $lat, $value['loc'][1], $value['loc'][0]);
            $value['distance'] = $distance;
        }

        return $this->sortByDistance($data);
    }

    //获得两个账户之间的距离
    public function getDistance($accountId, $targetId) {
        $info = UserCounter::getInfo($accountId);
        $lat1 = floatval($info->lat);
        $lng1 = floatval($info->lng);
        if (!LBS::validatLatlng($lat1, $lng1)) return false;

        $lat2 = floatval(UserCounter::getInfo($targetId)->lat);
        $lng2 = floatval(UserCounter::getInfo($targetId)->lng);
        if (!LBS::validatLatlng($lat2, $lng2)) return false;

        $distance = self::calcDistance($lat1, $lng1, $lat2, $lng2);
        return $distance;

    }

    public static function formatDistance($distance){
        if ($distance < ZERO_VALUE) {
            return '';
        }
        if ($distance < 1) {
            $dis = intval(abs($distance)* 1000) ;
            if ($dis == 0)
                $dis = 1;
            return  $dis . '米';
        }
        else
            return  intval(abs($distance)) . '千米';

    }

    public static function formatDistance2($distance) {
        $hundreds = intval($distance * 10) + 1;
        if ($hundreds < 10) {
            return "{$hundreds}00米以内";
        }
        else {
            $thousands = intval($distance) + 1;
            return "{$thousands}千米以内";
        }
    }
}
