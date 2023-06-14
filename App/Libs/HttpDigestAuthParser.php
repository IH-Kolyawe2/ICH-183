<?php

namespace App\Libs;

class HttpDigestAuthParser
{
    public static function parse($txt)
    {
        $needed_parts = ['nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1];
        $data = [];
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

        foreach($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }

        return $needed_parts ? false : $data;
    }
}