<?php

    class WooLockerModel {

        public $id;
        public $name;
        public $address;
        public $terms;
        public $shedules = null;

        function __construct($args = null){
            if($args !== null && is_array($args)){
                foreach($args as $key => $value){
                    $this->{'set'.ucfirst($key)}($value);
                }
            }
        }

        public function setId($id){
            $this->id = $id;
        }

        public function setName($name){
            $this->name = $name;
        }

        public function setAddress($address){
            $this->address = $address;
        }

        public function setTerms($terms){
            $this->terms = $terms;
        }

        public function setShedules($shedules){
            $this->shedules = $shedules;
        }

        public function getId(){
            return $this->id;
        }

        public function getName(){
            return $this->name;
        }

        public function getAddress(){
            return $this->address;
        }

        public function getTerms(){
            return $this->terms;
        }

        public function getShedules(){
            return $this->shedules;
        }

    }