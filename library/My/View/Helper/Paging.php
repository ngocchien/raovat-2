<?php

/**
 * @author      :   VuNCD
 * @name        :   Backend_View_Helper_Paging
 * @version     :   1.0
 * @copyright   :   FPT Online
 * @todo        :
 */

namespace My\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Paging extends AbstractHelper
{

    public function __invoke($strModule, $strController, $strAction, $intTotal, $intCurrentPage, $intLimit, $strRoute, $arrParams = array())
    {
        $paging = $this->paging($strModule, $strController, $strAction, $intTotal, $intCurrentPage, $intLimit, $strRoute, $arrParams);
        return $paging;
    }

    /**
     * Genegate HTML paging
     * @param <string> $strModule
     * @param <string> $strController
     * @param <string> $strAction
     * @param <array> $arrCondition
     * @param <int> $intTotal
     * @param <int> $intPage
     * @param <int> $intLimit
     * @param <string> $strRoute
     * @return <string> $result
     */
    public function paging($strModule, $strController, $strAction, $intTotal = 0, $intCurrentPage = 1, $intLimit = 15, $strRoute = null, $arrParams = array(), $str = 'kết quả')
    {
        $result = '';
        $urlHelper = '';
        $intTotal = (int)$intTotal;
        $intCurrentPage = (int)$intCurrentPage;
        $intLimit = (int)$intLimit;
        $strModule = strtolower($strModule);
        $strController = $strController;
        $strAction = strtolower($strAction);
        if (empty($strModule) || empty($strController) || empty($strAction) || $intTotal < 0) {
            return $result;
        }
        $intTotal > 0 && $intLimit > 0 ? $intTotalPage = ceil($intTotal / $intLimit) : $intTotalPage = 0;
        if ($intTotalPage < $intCurrentPage || $intTotalPage <= 1) {
            return $result;
        }
        $urlHelper = $this->view->plugin('url');
//        p($urlHelper);die;
        $arrCondition = array('controller' => $strController, 'action' => $strAction, 'page' => $intCurrentPage);
        $arrCondition = $arrParams ? $arrCondition + $arrParams : $arrCondition;


        if ($strModule === 'backend' || $strModule === 'partner') {
            $serverUrl = $urlHelper('home', array(), array('force_canonical' => true));
            $serverUrl = substr($serverUrl, 0, -1);
            $result .= '<div style="text-align:right;" class="row">';
            $result .= '<ul class="dataTables_paginate paging_bootstrap pagination" style=" padding: 0px;margin-bottom: 8px;">';
//            p($result);die;
            if ($intCurrentPage == 1) {
                $intPage = 1;
                $intLimitPage = 10;
            } else {
                $intPage = ($intCurrentPage > 5) ? ($intCurrentPage == $intTotalPage && $intTotalPage > 10) ? $intCurrentPage - 10 : $intCurrentPage - 5 : 1;
                $intLimitPage = ($intTotalPage < 11) ? $intTotalPage : $intCurrentPage + 5;
                $arrCondition['page'] = 1;
                $result .= '<li style="margin: 0 2px;border:0px;"><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">« Đầu</a></li>';
                $arrCondition['page'] = $intCurrentPage - 1;
                $result .= '<li  style="margin: 0 2px;border:0px;"><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">← Trước</a></li>';
            }
            for ($intPage; $intPage <= $intTotalPage && $intPage <= $intLimitPage; $intPage++) {
                $arrCondition['page'] = $intPage;
//                p($arrCondition);die;
                if ($intPage == $intCurrentPage) {
                    $result .= '<li  style="margin: 0 2px;border:0px;" class="active"><a style=""  href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">' . $intPage . '</a></li>';
                } else {
                    $result .= '<li  style="margin: 0 2px;border:0px;"><a rel="text" href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">' . $intPage . '</a></li>';
                }
            }
            if ($intCurrentPage == $intTotalPage) {
                $result .= '';
            } else {
                $arrCondition['page'] = $intCurrentPage + 1;
                $result .= '<li   style="margin: 0 2px;border:0px;"><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">Sau →</a></li>';
                $arrCondition['page'] = $intTotalPage;
                $result .= '<li   style="margin: 0 2px;border:0px;"><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">Cuối »</a></li>';
            }
            $result .= '</ul></div>';
            $result .= '<div style="text-align:right;">';
            $from = ($intLimit * ($intCurrentPage - 1)) + 1;
            $tmp1 = 'Hiển thị từ ' . $from . ' đến ';
            $tmp2 = ($intLimit * $intCurrentPage > $intTotal) ? number_format($intTotal, 0, ',', '.') : $intLimit * $intCurrentPage;
            $tmp3 = ' trong tổng số ' . number_format($intTotal, 0, ',', '.') . ' ' . $str;
            $from == $intTotal ? $result .= $str . ' cuối cùng trong tổng số ' . number_format($intTotal, 0, ',', '.') . ' ' . $str : $result .= $tmp1 . $tmp2 . $tmp3;
            $result .= '</div>';
            ##################################################
        } elseif ($strModule === 'frontend') {
//            $serverUrl = $urlHelper('home', array(), array('force_canonical' => true));
//            $serverUrl = substr($serverUrl, 0, -1);
//            $result .= '<div class="row"><div class="col-md-12 text-center">';
//            $result .= '<ul class="pagination" style="width:auto;">';
//            
//            
////            <div class="pagination magz-pagination"><span class='page-numbers current'>1</span>
////<a class='page-numbers' href='blog-page2.html'>2</a>
////<a class="next page-numbers" href="blog-page2.html">Next</a></div>
////                    
////                    <div class="pagination magz-pagination"><a class="prev page-numbers" href="blog.html">Previous</a>
////			<a class='page-numbers' href='blog.html'>1</a>
////			<span class='page-numbers current'>2</span></div>
//            if ($intCurrentPage == 1) {
//                $intPage = 1;
//                $intLimitPage = 10;
//            } else {
//                $intPage = ($intCurrentPage > 5) ? ($intCurrentPage == $intTotalPage && $intTotalPage > 10) ? $intCurrentPage - 10 : $intCurrentPage - 5 : 1;
//                $intLimitPage = ($intTotalPage < 11) ? $intTotalPage : $intCurrentPage + 5;
//                $arrCondition['page'] = 1;
//                $result .= '<li><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">đầu</a></li>';
//                $arrCondition['page'] = $intCurrentPage - 1;
//                $result .= '<li><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">trước</a></li>';
//           
//                }
//            for ($intPage; $intPage <= $intTotalPage && $intPage <= $intLimitPage; $intPage++) {
//                $arrCondition['page'] = $intPage;
//                if ($intPage == $intCurrentPage) {
//                    $result .= '<li class="active"><a style="cursor:pointer;">' . $intPage . '</a></li>';
//                } else {
//                    $result .= '<li><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">' . $intPage . '</a></li>';
//                }
//            }
//            if ($intCurrentPage == $intTotalPage) {
//                $result .= '';
//            } else {
//                $arrCondition['page'] = $intCurrentPage + 1;
//                $result .= '<li><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">sau</a></li>';
//                $arrCondition['page'] = $intTotalPage;
//                $result .= '<li><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">cuối</a></li>';
//            }
//           
//            $result .= '</ul></div></div>';


            $serverUrl = $urlHelper('home', array(), array('force_canonical' => true));
            $serverUrl = substr($serverUrl, 0, -1);
            $result .= '<div class="pagination-container"><ul class="pagination">';
            if ($intCurrentPage == 1) {
                $intPage = 1;
                $intLimitPage = 4;
            } else {
                $intPage = ($intCurrentPage > 2) ? ($intCurrentPage == $intTotalPage && $intTotalPage > 4) ? $intCurrentPage - 4 : $intCurrentPage - 2 : 1;
                $intLimitPage = ($intTotalPage < 6) ? $intTotalPage : $intCurrentPage + 2;
                $arrCondition['page'] = 1;
                $result .= '<li><a class="prev page-numbers" href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '"><< đầu</a></li>';
                $arrCondition['page'] = $intCurrentPage - 1;
                $result .= '<li class="PagedList-skipToPrevious"><a rel="prev" href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">trước</a></li>';

            }
            for ($intPage; $intPage <= $intTotalPage && $intPage <= $intLimitPage; $intPage++) {
                $arrCondition['page'] = $intPage;
                if ($intPage == $intCurrentPage) {
                    $result .= '<li class="active"><a>' . $intPage . '</a></li>';
                } else {
                    $result .= '<li><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">' . $intPage . '</a></li>';
                }
            }
            if ($intCurrentPage == $intTotalPage) {
                $result .= '';
            } else {
                $arrCondition['page'] = $intCurrentPage + 1;
                $result .= '<li class="PagedList-skipToNext"><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">sau</a></li>';
                $arrCondition['page'] = $intTotalPage;
                $result .= '<li><a class="next page-numbers" href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">cuối >></a></li>';
            }

            $result .= '</ul></div>';

        } elseif ($strModule === 'blog') {
            if ($intCurrentPage == 1) {
                $intPage = 1;
                $intLimitPage = 10;
            } else {
                $intPage = ($intCurrentPage > 5) ? ($intCurrentPage == $intTotalPage && $intTotalPage > 10) ? $intCurrentPage - 10 : $intCurrentPage - 5 : 1;
                $intLimitPage = ($intTotalPage < 11) ? $intTotalPage : $intCurrentPage + 5;
                $arrCondition['page'] = 1;
                $result .= '<a class="nextpostslink" href="' . $urlHelper($strRoute, $arrCondition) . '">Đầu</a>';
                $arrCondition['page'] = $intCurrentPage - 1;
                $result .= '<a class="last" href="' . $urlHelper($strRoute, $arrCondition) . '">Trước</a>';
            }

            for ($intPage; $intPage <= $intTotalPage && $intPage <= $intLimitPage; $intPage++) {
                $arrCondition['page'] = $intPage;
                if ($intPage == $intCurrentPage) {
                    $result .= '<span class="current">' . $intPage . '</span>';
                } else {
                    $result .= '<a class="page larger" href="' . $urlHelper($strRoute, $arrCondition) . '">' . $intPage . '</a>';
                }
            }
            if ($intCurrentPage == $intTotalPage) {
                $result .= '';
            } else {
                $arrCondition['page'] = $intCurrentPage + 1;
                $result .= '<a class="nextpostslink" href="' . $urlHelper($strRoute, $arrCondition) . '">Sau</a>';
                $arrCondition['page'] = $intTotalPage;
                $result .= '<a class="last" href="' . $urlHelper($strRoute, $arrCondition) . '">Cuối</a>';
            }
        }
        return $result;
    }

}
