<?php
    /*
    *   DB_BASE3 version 1.0
    *
    *   Imagina - Plugin.
    *
    *
    *   Copyright (c) 2012 Dolem Labs
    *
    *   Authors:    Paul Marclay (paul.eduardo.marclay@gmail.com)
    *
    */

	class Db_Record extends Ancestor {
        private $_model             = null;
        private $_fieldIndex        = null;
        private $_fieldIndexValue   = null;
        private $_data              = array();
        private $_hashData          = array();
        private $_relationFields    = array();
        
        // -- Constructor & Destructor
        
        public function __construct(&$model, $fieldIndex, $fieldIndexValue, $fieldsAndValues = array(), $relationFields = array()) {
            $this->_model           = $model;
            $this->_data            = $fieldsAndValues;
            $this->_fieldIndex      = $fieldIndex;
            $this->_fieldIndexValue = $fieldIndexValue;
            $this->_relationFields  = $relationFields;

            $this->_updateHashData();

            // Debugger::debug($this->getRelationFields());
		}
        
        public function __destruct() {
            foreach ($this as $index => $value){
	             unset($this->$index);
	        }
        }
        
        // -- Getters
        
        public function getModel() {
            return $this->_model;
        }

        public function getFieldIndex() {
            return $this->_fieldIndex;
        }
        
        public function getFieldIndexValue() {
            return $this->_fieldIndexValue;
        }
        
        public function getFieldCaption() {
            return $this->getModel()->getFieldCaption();
        }
        
        public function getRelationFields() {
            return $this->_relationFields;
        }
        
        // -- Setters
        
        public function setModel($model) {
            $this->_model = $model;
        }

        public function setFieldIndex($fieldIndex) {
            $this->_fieldIndex = $fieldIndex;
        }
        
        public function setFieldIndexValue($value) {
            $this->_fieldIndexValue = $value;
        }
        
        public function setRelationFields($relationFields) {
            $this->_relationFields = $relationFields();
        }
        
        // -- Auto for get/set/has fields values
        
        public function __call($method, $args) {
	    	switch (substr($method, 0, 3)) {
            case 'get' :
                $key    = Conversor::underscore(substr($method,3));
                $result = $this->_getData($key, isset($args[0]) ? $args[0] : null);
                return $result;

            case 'set' :
                $key    = Conversor::underscore(substr($method,3));
                $result = $this->_setData($key, isset($args[0]) ? $args[0] : null);
                return $result;
	    	
            case 'has' :
                $key = Conversor::underscore(substr($method,3));
                return $this->_hasData($key);
	    	}
	    }

        public function __set($name, $value) {
            $key    = Conversor::underscore($name);
            $result = $this->_setData($key, $value) ? $value : null;
            if ($result == null) {
                throw new Php_Exception("Field not found: $name");
            }
        }

        public function __get($name) {
            $key    = Conversor::underscore($name);
            $result = $this->_getData($key, isset($name) ? $name : null);

            if ($result == null && !$this->_hasData($name)) {
                throw new Php_Exception("Field not found: $name");
            }

            return $result;
        }
        
        public function _getData($key = '') {
	    	if ($this->_hasData($key)) {
                return $this->_data[$key];
            }
            
            if ($this->isRelationField($key)) {
                return $this->getModel()->createRelation($key, $this);
            }
            
            return null;
	    }
	    
	    public function _setData($key, $value = null) {
            if (!$this->_hasData($key)) {
                return null;
            }
            
            $this->_data[$key] = $value;
	        return $this;
	    }
        
        public function setDataArray($data = array(), $excludeFields = array()) {
            foreach ($data as $key => $value) {
                if (in_array($key, $excludeFields) || !$this->_hasData($key)) continue;
                $this->_setData($key, $value);
            }
            
            return $this;
        }
        
        protected function _hasData($key) {
            return in_array($key, array_keys($this->_data));
        }
        
        public function isRelationField($key) {
            return in_array($key, $this->_relationFields);
        }

        // protected function isDelegatedField($key) {
        //     //@TODO // hacer esto.
        // }
        
        // -- Misc methods
        
        public function isEmpty() {
	        return (empty($this->_data)) ? true : false;
	    }
        
        public function clear() {
	    	foreach ($this->_getData() as $key => $value)
            $this->_data[$key] = null;
	    }
        
        public function toArray() {
            return $this->_data;
        }
        
        public function getFieldNames() {
            return array_keys($this->_data);
        }

        public function hasChanged($field) {
            return !($this->_hashData[$field] == md5($this->_getData($field)));
        }

        public function updatedFieldsToArray() {
            $arrToRet = array();

            foreach ($this->toArray() as $key => $value) {
                if ($this->hasChanged($key)) {
                    $arrToRet[$key] = $value;
                }
            }

            return $arrToRet;
        }

        protected function _updateHashData() {
            foreach ($this->_data as $key => $value) {
                $this->_hashData[$key] = md5($value);
            }
        }
        
        // -- Model methods
        
        public function reload() {
            if (!$this->getFieldIndexValue()) {
                return null;
            }
            
            $tmpRecord = $this->getModel()->load($this->getFieldIndexValue());
            if (!$tmpRecord) {
                return null;
            }
            
            $this->_data = $tmpRecord->toArray();
            $this->_updateHashData();
            unset($tmpRecord);
            
            return $this;
        }
        
        public function save() {
            $this->setFieldIndexValue($this->getModel()->save($this));
            $this->_updateHashData();
            return $this->getFieldIndexValue();
        }
        
        public function delete() {
            return $this->getModel()->delete($this->getFieldIndexValue());
        }
        
        public function getFieldInfo($fieldName) {
            return $this->getModel()->getFieldInfo($fieldName);
        }
        
    }