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

	class Db_Collection extends Ancestor implements IteratorAggregate {
        private $_data      = array();
        private $_ids       = null;
        private $_modelName = null;
        
        public function __construct($modelName) {
            $this->setModelName($modelName);
        }
        
        public function __destruct() {
			foreach ($this as $index => $value){
				unset($this->$index);
			}
		}
        
        // -- Auto for get/set/has fields values
        
        public function __call($method, $args) {
            switch (substr($method, 0, 3)) {
            case 'get' :
                $key    = Conversor::underscore(substr($method,3));
                $result = array();
                foreach ($this as $record) {
                    $result[$record->getFieldIndexValue()] = $record->_getData($key, isset($args[0]) ? $args[0] : null);
                }
                return $result;

            case 'set' :
                $key    = Conversor::underscore(substr($method,3));
                foreach ($this as $record) {
                    $record->_setData($key,$args[0]);
                }
                
                return $this;
            }
        }

        // -- Collection
        
        public function add($record) {
			$recordId = $record->getFieldIndexValue();
	        if (is_null($recordId)) {
	            throw new Php_Exception('Record not have id!, this record is saved?');
	        }
            
            if (isset($this->_data[$recordId])) {
                throw new Php_Exception('Record: ('.get_class($record).') with the same id: "'.$record->getId().'" already exist!');
            }
            $this->addId($recordId);
            $this->_data[$recordId] = $record;
            
	        return $this;
		}
        
        public function getIterator() {
			return new ArrayIterator($this->_data);
		}
        
        private function addId($id) {
            $this->_ids .= (($this->getIds()) ? ",$id" : $id);
        }
        
        public function getIds() {
            return $this->_ids;
        }
        
        public function setIds($ids) {
            $this->_ids = $ids;
        }
        
        public function first() {
	        if (count($this->_data)) {
	            reset($this->_data);
	            return current($this->_data);
	        }
            
            return null;
	    }
	    
		public function last() {
	
	        if (count($this->_data)) {
	            return end($this->_data);
	        }
	
	        return null;
	    }
	    
	    public function clear() {
	    	$this->_data = array();
	    	return $this;
	    }
        
        public function getAllItemsToArray() {
            return $this->_data;
        }
        
        // -- Countable
        
	    public function count() {
	    	return count($this->_data);
	    }
	    
        public function sum($field) {
            $total = 0;
            
            foreach ($this as $record) {
                $total += $record->_getData($field);
            }
            
            return $total;
        }
        
        public function max($field) {
            $max = null;
            
            foreach ($this as $record) {
                if ($max == null || $record->_getData($field) > $max) {
                    $max = $record->_getData($field);
                }
            }
            
            return $max;
        }
        
        public function min($field) {
            $min = null;
            
            foreach ($this as $record) {
                if ($min == null || $record->_getData($field) < $min) {
                    $min = $record->_getData($field);
                }
            }
            
            return $min;
        }
        
        public function avg($field) {
            $total = 0;
            
            foreach ($this as $record) {
                $total += $record->_getData($field);
            }
            
            return ($total / $this->count());
        }
        
        // -- Model methods.
        
        public function save() {
            foreach ($this as $record) {
                $record->save();
            }

            return $this;
        }
        
        public function reload() {
            foreach ($this as $record) {
                $record->reload();
            }

            return $this;
        }
        
        public function delete() {
            foreach ($this as $record) {
                $record->delete();
            }

            return $this;
        }
        
        // -- Getters
        
        public function getModelName() {
            return $this->_modelName;
        }
        
        // -- Setters
        
        public function setModelName($modelName) {
            $this->_modelName = $modelName;
        }
    }