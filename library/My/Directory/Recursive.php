<?php

/**
 * WDS GROUP
 *
 * @name        Recursive.php
 * @category    WDS
 * @package        
 * @subpackage           
 * @author      Toan Pham Duc <toanbk@wds.vn>
 * @copyright   Copyright (c)2008-2012 WDS GROUP. All rights reserved
 * @license     http://wds.vn/license/     WDS Software License
 * @version     $1.0$
 * 10:34:19 AM Apr 27, 2012
 *
 * LICENSE
 *
 * This source file is copyrighted by WDS GROUP, full details in LICENSE.txt.
 * It is also available through the Internet at this URL:
 * http://wds.vn/license/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the Internet, please send an email
 * to license@wds.vn so we can send you a copy immediately.
 *
 */

namespace My\Directory;

use My\General;

class Recursive {

    protected $_sourceArr;
    protected $_primaryId;

    public function __construct($sourceArr = null, $primary_id = 'catalog_id') {
        $this->_sourceArr = $sourceArr;
        $this->_primaryId = $primary_id;
    }

    public function buildArray($parent_id = 0) {
        $resultArr = array();
        $this->recursive($this->_sourceArr, $parent_id, 1, $resultArr);

        return $resultArr;
    }

    public function buildMenu($parent_id = 0, $current_id = 0, $options = array()) {
        foreach ($this->_sourceArr as $value) {
            $menu_data['items'][$value[$this->_primaryId]] = $value;
            $menu_data['parent'][$value['parent_id']][] = $value[$this->_primaryId];
        }

        $result = $this->categoryTreeHtml($menu_data, $parent_id, $current_id, $options);
        return $result;
    }

    public function categoryTreeHtml($sourceArr, $parents = 0, $current_id = 0, $options = array(), &$level = 0) {
        $menu = '';
        if (isset($sourceArr['parent'][$parents])) {
            foreach ($sourceArr['parent'][$parents] as $item) {
                $name = $sourceArr['items'][$item]['catalog_name'];
                $id = $sourceArr['items'][$item]['catalog_id'];
                $categoryType = $sourceArr['items'][$item]['catalog_type'];

                if ($options['type'] == 'add') {
                    if ($level < 2) {
                    	$class = '';
                    	if ($parents == 0) {
                    		$class = 'main-catalog';
                    	}
                        $radio = '<input type="radio" name="parentID" value="' . $id . '" rel="' . $categoryType . '"><a href="#">' . $name . '</a>';
                    } else {
                        $class = '';
                        $radio = '<a href="#">' . $name . '</a>';
                    }
                }
                if ($options['type'] == 'edit') {
                    if ($options['parent_id'] == 0) {
                        if ($parents == 0) {
                            $class = 'main-category';
                        } else {
                            $class = '';
                        }
                        $radio = '<a href="#">' . $name . '</a>';
                    } else {
                        if ($level < 2) {
                        	$class = '';
                        	if ($parents == 0) {
                        		$class = 'main-catalog';
                        	}
                        	
                            if ($options['parent_id'] == $id) {
                                $checked = 'checked="checked"';
                            } else {
                                $checked = '';
                            }
                            $radio = '<input type="radio" name="parentID" value="' . $id . '" rel="' . $categoryType . '" ' . $checked . '><a href="#">' . $name . '</a>';
                        } else {
                            $class = '';
                            $radio = '<a href="#">' . $name . '</a>';
                        }
                    }
                }

                $menu .= '<li class="' . $class . '">';
                //$id = $sourceArr['items'][$item]['catalog_id'];
                //$menuLink = $action_link . '/id/' . $id . '/' . $url->urlHelper($name);
                //$selected = ($id == $current_id)?'class="visited"':'';

                $menu .= $radio;
                //$menu .= '<a href="#">' . $name . '</a>';

                $menu .= '<ul>';
                $level ++;
                $menu .= $this->categoryTreeHtml($sourceArr, $item, $current_id, $options, $level);
                $menu .= '</ul>';
                $menu .= '</li>';
            }
        }
        
        $level--;
        return $menu;
    }

    public function recursive($sourceArr, $parent_id = 0, $level = 1, &$resultArr) {
        if (count($sourceArr) > 0) {
            foreach ($sourceArr as $key => $value) {
                if ($value['parent'] == $parent_id) {
                    $value['level'] = $level;
                    $resultArr[] = $value;
                    $newparent_id = $value[$this->_primaryId];
                    unset($sourceArr[$key]);
                    $this->recursive($sourceArr, $newparent_id, $level + 1, $resultArr);
                }
            }
        }
    }

    public function categoryTree($sourceArr, $parent = 0, $selected = array()) {
        $resultArr = array();
        if (count($sourceArr) > 0) {
            foreach ($sourceArr as $key => $value) {
                if ($value['parent_id'] == $parent) {

                    $attr = array();
                    $state = 'close';

                    if (isset($selected[$value[$this->_primaryId]])) {
                        //$attr['class'] = 'jstree-checked';
                        $state = 'open';
                    }

                    if ($value['parent_id'] == 0) {
                        $attr['class'] = 'main-catalog';
                    }
                    $attr['id'] = $value[$this->_primaryId];
                    $attr['rel'] = $value['catalog_name'] . '|' . General::getCatalogType((int) $value['catalog_type']) . '|' . $value['meta_keyword'] . '|' . $value['meta_description'] . '|' . $value['ordering'];
                    $attr['value'] = $value['parent_id'];

                    $newParents = $value[$this->_primaryId];
                    unset($sourceArr[$key]);

                    $resultArr[] = array('attr' => $attr,
                        'data' => $value['catalog_name'],
                        'state' => $state,
                        'children' => $this->categoryTree($sourceArr, $newParents, $selected)
                    );
                }
            }
        }
        return $resultArr;
    }
    
    
    public function catalogArrayTree($sourceArr, $parent = 0, $selected = array()) {
        $resultArr = array();
        if (count($sourceArr) > 0) {
            foreach ($sourceArr as $key => $value) {
                if ($value['parent_id'] == $parent) {
                	
                	$data = $value;

                	$isSelected = false;
                    if (isset($selected[$value[$this->_primaryId]])) {
                        $isSelected = true;
                    }
                    
                    $data['selected'] = $isSelected;
                    

                    $newParents = $value[$this->_primaryId];
                    unset($sourceArr[$key]);
                    
                    $data['children'] = $this->catalogArrayTree($sourceArr, $newParents, $selected);
                    $resultArr[] = $data;
                }
            }
        }
        
        return $resultArr;
    }

}