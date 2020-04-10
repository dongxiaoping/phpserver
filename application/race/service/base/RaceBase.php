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
        if ((($majongInfo['one'] === 2) && ($majongInfo['two'] === 8)) || (($majongInfo['one'] === 8) && ($majongInfo['two'] === 2))) {
            return $MAJ_VALUE_TYPE['ER_BA_GANG'];
        }
        if ($majongInfo['one'] === $majongInfo['two']) {
            return $MAJ_VALUE_TYPE['DUI_ZI'];
        } else if ($majongInfo['two'] + $majongInfo['one'] === 10) {
            return $MAJ_VALUE_TYPE['BI_SHI'];
        } else {
            return $MAJ_VALUE_TYPE['DIAN'];
        }
    }

    public function isLandlordMahjongWin($landlordCount, $normalMemberCount)
    {
        $MAJ_VALUE_TYPE = json_decode(MAJ_VALUE_TYPE, true);
        $landlordValType = $this->getMajhongValueType($landlordCount);
        $normalMemberValType = $this->getMajhongValueType($normalMemberCount);
        if($landlordValType === $MAJ_VALUE_TYPE['DUI_ZI'] && $landlordCount['one'] == 0.5){
            $landlordCount['one'] = 10.5;
            $landlordCount['two'] = 10.5;
        }
        if($normalMemberValType === $MAJ_VALUE_TYPE['DUI_ZI'] && $normalMemberCount['one'] == 0.5){
            $normalMemberCount['one'] = 10.5;
            $normalMemberCount['two'] = 10.5;
        }

        if ($landlordValType === $MAJ_VALUE_TYPE['ER_BA_GANG']) {
            return true;
        }
        if ($normalMemberValType === $MAJ_VALUE_TYPE['ER_BA_GANG']) {
            return false;
        }

        if ($landlordValType === $MAJ_VALUE_TYPE['DUI_ZI']) {
            if ($normalMemberValType === $MAJ_VALUE_TYPE['DUI_ZI']) {
                if ($normalMemberCount['one'] > $landlordCount['one']) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }

        if ($landlordValType === $MAJ_VALUE_TYPE['BI_SHI']) {
            if ($normalMemberValType === $MAJ_VALUE_TYPE['BI_SHI']) {
                if (($normalMemberCount['one'] > $landlordCount['one'] && $normalMemberCount['one'] > $landlordCount['two'])
                    || ($normalMemberCount['two'] > $landlordCount['one'] && $normalMemberCount['two'] > $landlordCount['two'])) {
                    return false;
                }
                return true;
            }
            return false;
        }

        if ($landlordValType === $MAJ_VALUE_TYPE['DIAN']) {
            switch ($normalMemberValType) {
                case $MAJ_VALUE_TYPE['DUI_ZI']:
                    return false;
                    break;
                case $MAJ_VALUE_TYPE['DIAN']:
                    $landlordPonit = ($landlordCount['two'] + $landlordCount['one']) > 10 ?
                        ($landlordCount['two'] + $landlordCount['one'] - 10) : ($landlordCount['two'] + $landlordCount['one']);
                    $normalMemberPonit = ($normalMemberCount['two'] + $normalMemberCount['one']) > 10 ?
                        ($normalMemberCount['two'] + $normalMemberCount['one'] - 10) : ($normalMemberCount['two'] + $normalMemberCount['one']);

                    if ($landlordPonit > $normalMemberPonit) {
                        return true;
                    } else if ($landlordPonit < $normalMemberPonit) {
                        return false;
                    } else { //点数相等情况下的判断
                        if ($landlordCount['one'] === $normalMemberCount['one'] || $landlordCount['one'] === $normalMemberCount['two']) {
                            return true;
                        } else if (($landlordCount['one'] > $normalMemberCount['one'] && $landlordCount['one'] > $normalMemberCount['two'])
                            || ($landlordCount['two'] > $normalMemberCount['one'] && $landlordCount['two'] > $normalMemberCount['two'])
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
        }
        return true;
    }
}