<?php

namespace App\Database\Query\Grammars;

class MariaDBGrammar extends \Illuminate\Database\Query\Grammars\MariaDbGrammar
{
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u';
    }
}