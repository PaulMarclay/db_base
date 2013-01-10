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
    
    class Db extends Ancestor {
    	public static function getModel($modelName, $fieldIndex = 'id') {
            $modelName = Conversor::uc_words($modelName, '', '_');
            return new $modelName;
		}
    }