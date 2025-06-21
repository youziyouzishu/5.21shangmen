<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;
use support\Cache;
use support\Redis;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

/**
 * 
 *
 * @property int $id ID
 * @property int|null $pid 父id
 * @property string|null $shortname 简称
 * @property string|null $name 名称
 * @property string|null $mergename 全称
 * @property int|null $level 层级:1=省,2=市,3=区/县
 * @property string|null $pinyin 拼音
 * @property string|null $code 长途区号
 * @property string|null $zip 邮编
 * @property string|null $first 首字母
 * @property string|null $lng 经度
 * @property string|null $lat 纬度
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area query()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Area> $children
 * @mixin \Eloquent
 */
class Area extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_area';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 根据经纬度获取当前地区信息
     * @param string $lng 经度
     * @param string $lat 纬度
     * @return Area 城市信息
     */
    public static function getAreaFromLngLat($lng, $lat, $level = 3)
    {
        // 添加缓存标记，避免重复执行
        if (Cache::get('geo_area_level_' . $level) !== true) {
            self::area2RedisGeo($level);
        }
        // 创建一个支持标签的缓存适配器
        $namearr = [1 => 'geo:province', 2 => 'geo:city', 3 => 'geo:district'];
        $rangearr = [1 => 15000, 2 => 1000, 3 => 200];
        $geoname = $namearr[$level] ?? $namearr[3];
        $georange = $rangearr[$level] ?? $rangearr[3];
        // 读取范围内的ID
        $georadiuslist = Redis::georadius($geoname, $lng, $lat, $georange, 'km', ['WITHDIST', 'COUNT' => 5, 'ASC']);
        if ($georadiuslist) {
            list($id, $distance) = $georadiuslist[0];
        }
        $id = isset($id) && $id ? $id : 3;
        return self::find($id);
    }

    /**
     * Name: 区域经纬度到redis
     * Date: 2021/10/20 14:21
     * @param $level
     */
    public static function area2RedisGeo($level) {
        $areas = self::where(['level' => $level])->get();
        $namearr = [1 => 'geo:province', 2 => 'geo:city', 3 => 'geo:district'];
        foreach ($areas as $area) {
            Redis::geoadd($namearr[$area['level']], $area['lng'], $area['lat'], $area['id']);
        }
        Cache::set('geo_area_level_' . $level, true);
    }

    /**
     * 根据经纬度获取省份
     *
     * @param string $lng 经度
     * @param string $lat 纬度
     * @return Area
     */
    public static function getProvinceFromLngLat($lng, $lat)
    {
        $provincedata = null;
        $citydata = self::getCityFromLngLat($lng, $lat);
        if ($citydata) {
            $provincedata = self::find($citydata['pid']);
        }
        return $provincedata;
    }

    /**
     * 根据经纬度获取城市
     *
     * @param string $lng 经度
     * @param string $lat 纬度
     * @return Area
     */
    public static function getCityFromLngLat($lng, $lat)
    {
        $citydata = null;
        $districtdata = self::getDistrictFromLngLat($lng, $lat);
        if ($districtdata) {
            $citydata = self::find($districtdata['pid']);
        }
        return $citydata;
    }

    /**
     * 根据经纬度获取地区
     *
     * @param string $lng 经度
     * @param string $lat 纬度
     * @return Area
     */
    public static function getDistrictFromLngLat($lng, $lat)
    {
        return self::getAreaFromLngLat($lng, $lat, 3);
    }

    /**
     * 根据经纬度获取距离
     * @param $lng1
     * @param $lat1
     * @param $lng2
     * @param $lat2
     * @return float|int
     */
    public static function getDistanceFromLngLat($lng1, $lat1, $lng2, $lat2)
    {
        $earthRadius = 6371; // 地球半径，单位：公里

        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);

        $dLat = $lat2 - $lat1;
        $dLng = $lng2 - $lng1;

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos($lat1) * cos($lat2) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return $distance;
    }



}
