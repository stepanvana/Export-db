<?php

//Require composer autoload
require('vendor/autoload.php');
use Granam\CzechVocative\CzechName;

class API {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function my_vocative($full_name)
    {
        $subjects = $this->detect_subjects($full_name);

        $array = array();
        
        foreach($subjects as $key => $subject) {
            $company = $this->detect_company($subject);

            if($company != NULL) {
                $company_name = trim(str_replace($company, "", $subject));

                $array[] = array(
                    'full_name'     => $subject,
                    'company'       => $company,
                    'company_name'  => $company_name
                );
            } else {
                $degrees = $this->detect_degrees($subject);
                $excluded = $this->detect_excluded($subject);

                $full_name = str_replace($excluded, "", $subject);
                foreach($degrees as $degree) {
                    $full_name = str_replace($degree, "", $full_name);
                }

                $individual = $this->split_name($full_name);

                $name = new CzechName();
                if($name->isMale($individual['first_name']) && $name->isMale($individual['last_name'])) {
                    $sex = 'man';
                } else {
                    $sex = 'woman';
                }

                $array[] = array(
                    'full_name'             => $subject,
                    'first_name'            => $individual['first_name'],
                    'middle_name'           => $individual['middle_name'],
                    'last_name'             => $individual['last_name'],
                    'degree'                => $degrees,
                    'pohlavi'               => $sex,
                    'vocative_first_name'   => $name->vocative($individual['first_name']),
                    'vocative_last_name'    => $name->vocative($individual['last_name']),
                    'excluded'              => $excluded
                ); 
            }
        }



        var_dump($array);
        //echo json_encode($array);
        die();
    }

    public function detect_subjects($full_name) {
        $query = "SELECT connecting_shrt FROM connecting_phrases";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll();

            foreach($result as $row) {
                $subjects = explode($row['connecting_shrt'], $full_name);
            }

        } else {
            echo json_encode(
                array(
                    'code'    => 5,
                    'message' => 'Error getting connecting phrases.'
                )
            );
        
            die();
        }

        return $subjects;
    }

    public function detect_degrees($subject) {
        $query = "SELECT degree_shrt FROM degree";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll();

            $degrees = array();

            foreach($result as $row) {
                if(strpos($subject, $row['degree_shrt']) == true) {
                    $degrees[] = $row['degree_shrt'];
                }
            }

        } else {
            echo json_encode(
                array(
                    'code'    => 6,
                    'message' => 'Error getting degrees.'
                )
            );
        
            die();
        }

        return $degrees;
    }

    public function detect_company($subject) {
        $company = NULL;

        $query = "SELECT company_shrt FROM company";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll();

            foreach($result as $row) {
                if(strpos($subject, $row['company_shrt']) == true) {
                    $company = $row['company_shrt'];
                }
            }

        } else {
            echo json_encode(
                array(
                    'code'    => 7,
                    'message' => 'Error getting company.'
                )
            );
        
            die();
        }

        return $company;
    }

    public function detect_excluded($subject) {
        $excluded = array();

        $subject = 'A ' . $subject;

        $query = "SELECT excluded_shrt FROM excluded_phrases";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $result = $stmt->fetchAll();

            foreach($result as $row) {
                if(strpos($subject, $row['excluded_shrt']) == true) {
                    $excluded[] = $row['excluded_shrt'];
                }
            }

        } else {
            echo json_encode(
                array(
                    'code'    => 8,
                    'message' => 'Error getting excluded.'
                )
            );
        
            die();
        }

        if(empty($excluded)) {
            return NULL;
        } else {
            return $excluded;
        }
    }

    public function split_name($full_name) {
        $full_name = trim($full_name);
        $arr = explode(' ', $full_name);
        $num = count($arr);
        $first_name = $middle_name = $last_name = null;
    
        if ($num == 2) {
            list($last_name, $first_name) = $arr;
        } else {
            list($last_name, $middle_name, $first_name) = $arr;
        }
    
        return (empty($first_name) || $num > 3) ? false : compact(
            'last_name', 'middle_name', 'first_name'
        );
    }
}