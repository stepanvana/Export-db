<?php

//Require composer autoload
require('vendor/autoload.php');

class API {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;

        
        for($i=0;$i<45000;$i++) {
            $query = "SELECT product.p_id, product_variant.pv_name, product.p_code AS product_code, product_variant.pv_code, product.p_name, product.p_suffix, product.p_desc_short, product.p_desc, brand.brand_name, product.p_meta_desc, 
            product.p_ean, CONCAT(main_image.fs_hash, '.', main_image.fs_ext) AS fs_original_name, GROUP_CONCAT(DISTINCT CONCAT(image.fs_hash, '.', image.fs_ext) SEPARATOR ';') AS images, 
            GROUP_CONCAT(DISTINCT parent_cats.cat_name ORDER BY parent_cats.cat_parent ASC SEPARATOR ';') AS parent_categories, main_cat.cat_name AS main_cat, 
            product.p_price_eshop, 1 AS priceRatio, 
            product.p_price_standard AS original_price, product.p_price_buy, currency.c_code, product.p_vat, product.p_minimum_amount, 
            GROUP_CONCAT(DISTINCT filter.f_name ORDER BY filter.f_name SEPARATOR ';') as filter_name,
            GROUP_CONCAT(DISTINCT CONCAT(filter_header.f_name, ' - ', filter_var.fv_name) ORDER BY filter_var.fv_id SEPARATOR ';') AS filter_var_name,
            product.p_available, product.p_warranty, product.p_weight, GROUP_CONCAT(DISTINCT related.p_code SEPARATOR ';') AS related_code, 
            product.p_active, product.p_tag_action, product.p_tag_new, product.p_tag_new_term_date, product.p_meta_name, product.p_heureka_ppc, product.p_zbozi_ppc, product.p_zbozi_ppc_search,
            seo.seo_url

            FROM product
            LEFT JOIN product_variant ON product_variant.p_id = product.p_id
            LEFT JOIN brand ON brand.brand_id = product.brand_id
            LEFT JOIN file main_image ON main_image.fs_id = product.p_picture
            LEFT JOIN file image ON image.fs_key = product.p_id
            LEFT JOIN product_category ON product_category.p_id = product.p_id
            LEFT JOIN product_category pc_main ON pc_main.p_id = product.p_id AND pc_main.pc_main = 1
            LEFT JOIN category AS cat ON product_category.cat_id = cat.cat_id
            LEFT JOIN category main_cat ON main_cat.cat_id = pc_main.cat_id
            LEFT JOIN category parent_cats ON parent_cats.cat_id = cat.cat_parent
            LEFT JOIN currency ON currency.c_id = product.p_type
            LEFT JOIN filter ON TRUE
            LEFT JOIN filter_product ON filter_product.p_id = product.p_id
            LEFT JOIN filter filter_header ON filter_header.f_id = filter_product.f_id
            LEFT JOIN filter_product_var ON filter_product_var.fp_id = filter_product.fp_id
            LEFT JOIN filter_var ON filter_var.fv_id = filter_product_var.fv_id
            LEFT JOIN product_assoc ON product_assoc.passoc_parent = product.p_id
            LEFT JOIN product related ON related.p_id = product_assoc.passoc_child
            LEFT JOIN seo ON seo.seo_key = product.p_id AND seo.seo_main = 1

            WHERE product.p_id = '$i'
            GROUP BY product_variant.pv_code, product.p_id
            ORDER BY product.p_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                $result[] = $stmt->fetchAll();
            }
        }

        foreach($result as $res) {
            foreach($res as $re) {
                $r[] = $re;
            }
        }

        foreach($r as $key => $value) {
            $export[$key] = [
                'ID' => $value['p_id'],
                'pv_name' => $value['pv_name'],
                'code' => $value['product_code'],
                'pv_code' => $value['pv_code'],
                'p_name' => $value['p_name'],
                'p_suffix' => $value['p_suffix'],
                'p_desc_short' => $value['p_desc_short'],
                'p_desc' => $value['p_desc'],
                'brand_name' => $value['brand_name'],
                'p_meta_desc' => $value['p_meta_desc'],
                'p_ean' => $value['p_ean'],
                'fs_original_name' => 'https://www.skiandbike.cz/files/xnbw3/1/b/' . $value['fs_original_name'],
                'images' => array_unique(explode(';', $value['images'])),
                'parent_categories' => array_unique(explode(';', $value['parent_categories'])),
                'main_cat' => $value['main_cat'],
                'p_price_eshop' => $value['p_price_eshop'],
                'priceRatio' => $value['priceRatio'],
                'original_price' => $value['original_price'],
                'p_price_buy' => $value['p_price_buy'],
                'c_code' => $value['c_code'],
                'p_vat' => $value['p_vat'],
                'p_minimum_amount' => $value['p_minimum_amount'],
                'filter_name' => array_unique(explode(';', $value['filter_name'])),
                'filter_var_name' => explode(';', $value['filter_var_name']),
                'p_available' => $value['p_available'],
                'p_warranty' => $value['p_warranty'],
                'p_weight' => $value['p_weight'],
                'related_code' => array_unique(explode(';', $value['related_code'])),
                'p_active' => $value['p_active'],
                'p_tag_action' => $value['p_tag_action'],
                'p_tag_new' => $value['p_tag_new'],
                'p_tag_new_term_date' => $value['p_tag_new_term_date'],
                'p_meta_name' => $value['p_meta_name'],
                'p_heureka_ppc' => $value['p_heureka_ppc'],
                'p_zbozi_ppc' => $value['p_zbozi_ppc'],
                'p_zbozi_ppc_search' => $value['p_zbozi_ppc_search'],
                'seo_url' => $value['seo_url']
            ];
        }
        
        $max_images = 0;
        $max_related_code = 0;

        foreach($export as $row) {
            foreach($row as $key2 => $row2) {
                if($key2 == 'images' && count($row2) > $max_images) {
                    $max_images = count($row2);
                } elseif($key2 == 'related_code' && count($row2) > $max_related_code) {
                    $max_related_code = count($row2);
                }
            }
        }

        $header[] = 'URL';
        $header[] = 'pv_name';
        $header[] = 'code';
        $header[] = 'pv_code';
        $header[] = 'name';
        $header[] = 'appendix';
        $header[] = 'shortDescription';
        $header[] = 'description';
        $header[] = 'manufacturer';
        $header[] = 'metaDescription';
        $header[] = 'ean';
        $header[] = 'defaultImage';
        for($i=1;$i<=$max_images;$i++) {
            $header[] = 'images' . $i;
        }
        $header[] = 'defaultCategory';
        $header[] = 'price';
        $header[] = 'priceRatio';
        $header[] = 'standartPrice';
        $header[] = 'purchasePrice';
        $header[] = 'currency';
        $header[] = 'percentVat';
        $header[] = 'minimumAmount';
        foreach($export[0]['filter_name'] as $row) {
            $header[] = $row;
        }
        $header[] = 'stock';
        $header[] = 'warranty';
        $header[] = 'weight';
        for($i=1;$i<=$max_related_code;$i++) {
            $header[] = 'related' . $i;
        }
        $header[] = 'productVisibility';
        $header[] = 'action';
        $header[] = 'new';
        $header[] = 'newDateTo';
        $header[] = 'seoTitle';
        $header[] = 'heurekaCpc';
        $header[] = 'zboziCpc';
        $header[] = 'zboziSearchCpc';
        $header[] = 'seoURL';

        header('Content-Encoding: UTF-8');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="sample.csv"');

        $fp = fopen('php://output', 'w');
        fputcsv($fp, $header);
        foreach($export as $line) {
            foreach($line as $line_key2 => $line2) {
                $categories = '';
                if($line_key2 == 'images') {
                    for($i=0;$i<$max_images;$i++) {
                        if(array_key_exists($i, $line2)) {
                            $actual_line[] = 'https://www.skiandbike.cz/files/xnbw3/1/b/' . $line2[$i];
                        } else {
                            $actual_line[] = '';
                        }
                    }
                } elseif($line_key2 == 'parent_categories') {
                        foreach($line2 as $parent_cat) {
                            $categories .= $parent_cat . ' > ';
                        }
                        $actual_line['cat'] = $categories;
                } elseif($line_key2 == 'main_cat') {
                        $categories .= $line2;
                        $actual_line['cat'] = $actual_line['cat'] . $categories;
                } elseif($line_key2 == 'filter_name') {
                    continue;
                } elseif($line_key2 == 'filter_var_name') {
                    foreach($line2 as $key => $row) {
                        if($row !== '') {
                            $param = explode(' - ', $row);
                            $par[$param[0]][$key] = $param[1];    
                        }
                    }                    
            
                    foreach($export[0]['filter_name'] as $head) {
                        if(isset($par)) {
                            if(array_key_exists($head, $par)) {
                                $vall = implode(',', $par[$head]);
                                $actual_line[] = $vall;
                                $vall = '';
                            } else {
                                $actual_line[] = '';
                            }    
                        } else {
                            $actual_line[] = '';
                        }
                    }
                    unset($par);
                } elseif($line_key2 == 'related_code') {
                    for($i=0;$i<$max_related_code;$i++) {
                        if(array_key_exists($i, $line2)) {
                            $actual_line[] = $line2[$i];
                        } else {
                            $actual_line[] = '';
                        }
                    }
                } 
                elseif($line_key2 == 'p_warranty') {
                    if($line2 == 10) {
                        $actual_line[] = '24 měsíců';
                    } elseif($line2 == 5) {
                        $actual_line[] = '12 měsíců';
                    }
                } else {
                    $actual_line[] = $line2;
                }
            }
            fputcsv($fp, $actual_line);
            unset($actual_line);
        }
        fclose($fp);

        die();
    }
}
