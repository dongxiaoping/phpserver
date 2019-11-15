<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2019/11/15
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------

namespace app\race\service\base;
class RaceBase
{

    /*            $majongResult = [
                'landlord'=>['one' => $majongs[0], 'two' => $majongs[1]],
                'sky' => ['one' => $majongs[2], 'two' => $majongs[3]],
                'middle' => ['one' => $majongs[4], 'two' => $majongs[5]],
                'land' => ['one' => $majongs[6], 'two' => $majongs[7]]
            ];*/
    public function getLocationResultDetail($majongResult)
    {
        $COMPARE_DX_RE = json_decode(COMPARE_DX_RE, true);
        $locationResultDetail = [
            'sky' => '',
            'land' => '',
            'middle' => '',
            'bridg' => '',
            'sky_corner' => '',
            'land_corner' => ''
        ];
        $isSkyWin = !$this->isLandlordMahjongWin($majongResult['landlord'], $majongResult['sky']);
        $isLandWin = !$this->isLandlordMahjongWin($majongResult['landlord'], $majongResult['land']);
        $isMiddleWin = !$this->isLandlordMahjongWin($majongResult['landlord'], $majongResult['middle']);
        if ($isSkyWin) {
            $locationResultDetail['sky'] = $COMPARE_DX_RE['BIG'];
        } else {
            $locationResultDetail['sky'] = $COMPARE_DX_RE['SMALL'];
        }
        if ($isLandWin) {
            $locationResultDetail['land'] = $COMPARE_DX_RE['BIG'];
        } else {
            $locationResultDetail['land'] = $COMPARE_DX_RE['SMALL'];
        }
        if ($isMiddleWin) {
            $locationResultDetail['middle'] = $COMPARE_DX_RE['BIG'];
        } else {
            $locationResultDetail['middle'] = $COMPARE_DX_RE['SMALL'];
        }

        if ($isSkyWin && $isLandWin) {
            $locationResultDetail['bridg'] = $COMPARE_DX_RE['BIG'];
        } else if (!$isSkyWin && !$isLandWin) {
            $locationResultDetail['bridg'] = $COMPARE_DX_RE['SMALL'];
        } else {
            $locationResultDetail['bridg'] = $COMPARE_DX_RE['EQ'];
        }

        if ($isSkyWin && $isMiddleWin) {
            $locationResultDetail['sky_corner'] = $COMPARE_DX_RE['BIG'];
        } else if (!$isSkyWin && !$isMiddleWin) {
            $locationResultDetail['sky_corner'] = $COMPARE_DX_RE['SMALL'];
        } else {
            $locationResultDetail['sky_corner'] = $COMPARE_DX_RE['EQ'];
        }

        if ($isMiddleWin && $isLandWin) {
            $locationResultDetail['land_corner'] = $COMPARE_DX_RE['BIG'];
        } else if (!$isMiddleWin && !$isLandWin) {
            $locationResultDetail['land_corner'] = $COMPARE_DX_RE['SMALL'];
        } else {
            $locationResultDetail['land_corner'] = $COMPARE_DX_RE['EQ'];
        }
        return $locationResultDetail;
    }

    public function get_mahjonList_by_race_count($race_count)
    {
        $mahjong_count = 8 * $race_count;
        $majong_pari_count = ceil($mahjong_count / 40);
        $majong_list = [];
        for ($i = 1; $i <= $majong_pari_count; $i++) {
            $the_points = $this->get_majong_pair();
            $majong_list = array_merge_recursive($majong_list, $the_points);
        }
        return $majong_list;
    }

    public function get_majong_pair()
    {
        $points = [];
        for ($i = 0; $i <= 9; $i++) {
            $val = $i == 0 ? 0.5 : $i;
            $points[] = $val;
            $points[] = $val;
            $points[] = $val;
            $points[] = $val;
        }
        shuffle($points);
        return $points;
    }

    public function getMajhongValueType($majongInfo)
    {
        $MAJ_VALUE_TYPE = json_decode(MAJ_VALUE_TYPE, true);
        if ($majongInfo['one'] === $majongInfo['two']) {
            return $MAJ_VALUE_TYPE['DUI_ZI'];
        } else if ($majongInfo['two'] + $majongInfo['one'] === 10) {
            return $MAJ_VALUE_TYPE['BI_SHI'];
        } else {
            return $MAJ_VALUE_TYPE['DIAN'];
        }
    }

    public function isLandlordMahjongWin($landlordCount, $compareToCount)
    {
        $MAJ_VALUE_TYPE = json_decode(MAJ_VALUE_TYPE, true);
        $targerType = $this->getMajhongValueType($landlordCount);
        $compareToType = $this->getMajhongValueType($compareToCount);
        switch ($targerType) {
            case $MAJ_VALUE_TYPE['DUI_ZI']:
                if($compareToType === $MAJ_VALUE_TYPE['DUI_ZI']){
                    if ($compareToCount['one'] > $landlordCount['one']) {
                        return false;
                    } else {
                        return true;
                    }
                }else{
                    return true;
                }
                break;
            case $MAJ_VALUE_TYPE['BI_SHI']:
                return true;
                break;
            case $MAJ_VALUE_TYPE['DIAN']:
                switch ($compareToType) {
                    case $MAJ_VALUE_TYPE['DUI_ZI']:
                        return false;
                        break;
                    case $MAJ_VALUE_TYPE['DIAN']:
                        $targerPonit = $landlordCount['two'] + $landlordCount['one'];
                        if ($targerPonit > 10) {
                            $targerPonit = $targerPonit - 10;
                        }
                        $compareToPonit = $compareToCount['two'] + $compareToCount['one'];
                        if ($compareToPonit > 10) {
                            $compareToPonit = $compareToPonit - 10;
                        }
                        if ($targerPonit > $compareToPonit) {
                            return true;
                        } else if ($targerPonit < $compareToPonit) {
                            return false;
                        } else { //点数相等情况下的判断
                            if ($landlordCount['one'] === $compareToCount['one'] || $landlordCount['one'] === $compareToCount['two']) {
                                return true;
                            } else if (($landlordCount['one'] > $compareToCount['one'] && $landlordCount['one'] > $compareToCount['two'])
                                || ($landlordCount['two'] > $compareToCount['one'] && $landlordCount['two'] > $compareToCount['two'])
                            ) {
                                return true;
                            } else {
                                return false;
                            }
                        }
                        break;
                    case $MAJ_VALUE_TYPE['BI_SHI']:
                        return true;
                        break;
                }
                break;
        }
        return true;
    }
}