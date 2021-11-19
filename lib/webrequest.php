<?php

//include ("parser/simple_html_dom.php");

class WebRequest {
    var $CookieName = "cookie.txt";

    var $Proxy = "";

    var $info;

    function __construct ($cookeFile, $httpProxy = "") {

        if ($cookeFile) {
            $this->CookieName = "cookies/" . $cookeFile . ".txt";
        }

        if (!empty($httpProxy)) {
            $this->Proxy = $httpProxy;
        }
    }

    function clear () {
        //clear cookies
        file_put_contents(dirname(__FILE__) . "/" . $this->CookieName, "");
    }

    // Send post request
    function Post ($url, $data, $referer = "", $headers = array()) {

        $headers[] = "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers[] = "Accept-Charset:ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $headers[] = "Accept-Language:en-us,en;q=0.5";
        //$headers[] = "Accept-Encoding:gzip";
        $headers[] = "Connection:keep-alive";
        $headers[] = "Keep-Alive:115";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1)");
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E)");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__) . "/" . $this->CookieName);
        curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__) . "/" . $this->CookieName);
        //curl_setopt($ch, CURLOPT_VERBOSE, true);
        // set proxy
        if (!empty($this->Proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $this->Proxy);
        }
        // set referer url
        if (!empty($referer)) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        $response = curl_exec($ch);

        //$this->info = curl_getinfo($ch);
        //echo $response;
        //echo $this->gzdecode($response);
        //print_r($this->info);

        curl_close($ch);

        return $response;
    }

    // Send get request
    function Get ($url, $referer = "", $headers = array()) {

        $headers[] = "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $headers[] = "Accept-Charset:ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $headers[] = "Accept-Language:en-us,en;q=0.5";
        //$headers[] = "Accept-Encoding:gzip";
        $headers[] = "Connection:keep-alive";
        $headers[] = "Keep-Alive:115";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1)");
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E)");

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // set proxy
        if (!empty($this->Proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $this->Proxy);
        }
        // set referer url
        if (!empty($referer)) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__) . "/" . $this->CookieName);
        curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__) . "/" . $this->CookieName);
        //curl_setopt($ch, CURLOPT_VERBOSE, true);

        $response = curl_exec($ch);
        //echo "<hr>";
        $this->info = curl_getinfo($ch);
        //print_r($this->info);
        //echo $response;
        //echo $this->gzdecode($response);
        curl_close($ch);

        return $response;
    }

    // serialize given form varibles, fill fomr field with given vales and remove unnessasary fields
    function Serialize ($html, $objects, $formIndex = 0) {
        // extract form parameters and serialize it
        $dom = str_get_html($html);

        $out = "";
        $form = $dom->find("form", $formIndex);

        if (empty($form)) {
            return "";
        }
        // TO DO : add select element if nessasary
        foreach ($form->find("input") as $input) {
            if ($input->type == 'button' || $input->type == 'submit') {
                continue;
            }

            if (isset($objects[$input->name])) {

                if ($objects[$input->name] == '-remove') {
                    continue;
                }

                $out .= $input->name . "=" . $objects[$input->name] . "&";
            } else {
                $out .= $input->name . "=" . $input->value . "&";
            }
        }

        //echo $out;
        return trim($out, "&");
    }

    // find form submit url
    function GetActionUrl ($html, $formIndex = 0) {

        $dom = str_get_html($html);

        $out = "";
        $form = $dom->find("form", $formIndex);
        if (!empty($form)) {
            return $form->action;
        } else {
            return "";
        }
    }

    function GetBaseUrl ($html) {

        $dom = str_get_html($html);
        $base = $dom->find("base", 0);

        if (!empty($base)) {
            return $base->href;
        } else {
            return "";
        }
    }

    // check whether given username has a live cookie (a cookie created within last 15 min)
    function HasLiveCookie () {

        if (!empty($this->CookieName)) {
            if (is_file(dirname(__FILE__) . "/" . $this->CookieName)) {
                $modified = filemtime(dirname(__FILE__) . "/" . $this->CookieName);

                if ((time() - $modified) < 1800) {
                    //echo ("yes:".(time()-$modified));
                    return true;
                } else {
                    //echo ("no:".(time()-$modified));
                    return false;
                }
            } else {
                return false;
            }
        } else {
            //echo ("Not found:");
            return false;
        }
    }

    // check whether the given element in given html fragment
    function HasElement ($html, $selector, $index = 0) {

        $dom = str_get_html($html);
        $element = $dom->find($selector, $index);
        if (!empty($element)) {
            //echo "has element";
            return true;
        } else {
            return false;
        }
    }

    // check whether the given text in html fragment
    function HasText ($html, $needle) {

        if (strpos($html, $needle) >= 0) {
            //echo "has text";
            return true;
        } else {
            return false;
        }
    }

    function gzdecode ($data) {
        $len = strlen($data);
        if ($len < 18 || strcmp(substr($data, 0, 2), "\x1f\x8b")) {
            return null;  // Not GZIP format (See RFC 1952)
        }
        $method = ord(substr($data, 2, 1));  // Compression method
        $flags = ord(substr($data, 3, 1));  // Flags
        if ($flags & 31 != $flags) {
            // Reserved bits are set -- NOT ALLOWED by RFC 1952
            return null;
        }
        // NOTE: $mtime may be negative (PHP integer limitations)
        $mtime = unpack("V", substr($data, 4, 4));
        $mtime = $mtime[1];
        $xfl = substr($data, 8, 1);
        $os = substr($data, 8, 1);
        $headerlen = 10;
        $extralen = 0;
        $extra = "";
        if ($flags & 4) {
            // 2-byte length prefixed EXTRA data in header
            if ($len - $headerlen - 2 < 8) {
                return false;    // Invalid format
            }
            $extralen = unpack("v", substr($data, 8, 2));
            $extralen = $extralen[1];
            if ($len - $headerlen - 2 - $extralen < 8) {
                return false;    // Invalid format
            }
            $extra = substr($data, 10, $extralen);
            $headerlen += 2 + $extralen;
        }

        $filenamelen = 0;
        $filename = "";
        if ($flags & 8) {
            // C-style string file NAME data in header
            if ($len - $headerlen - 1 < 8) {
                return false;    // Invalid format
            }
            $filenamelen = strpos(substr($data, 8 + $extralen), chr(0));
            if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
                return false;    // Invalid format
            }
            $filename = substr($data, $headerlen, $filenamelen);
            $headerlen += $filenamelen + 1;
        }

        $commentlen = 0;
        $comment = "";
        if ($flags & 16) {
            // C-style string COMMENT data in header
            if ($len - $headerlen - 1 < 8) {
                return false;    // Invalid format
            }
            $commentlen = strpos(substr($data, 8 + $extralen + $filenamelen), chr(0));
            if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
                return false;    // Invalid header format
            }
            $comment = substr($data, $headerlen, $commentlen);
            $headerlen += $commentlen + 1;
        }

        $headercrc = "";
        if ($flags & 2) {
            // 2-bytes (lowest order) of CRC32 on header present
            if ($len - $headerlen - 2 < 8) {
                return false;    // Invalid format
            }
            $calccrc = crc32(substr($data, 0, $headerlen)) & 0xffff;
            $headercrc = unpack("v", substr($data, $headerlen, 2));
            $headercrc = $headercrc[1];
            if ($headercrc != $calccrc) {
                return false;    // Bad header CRC
            }
            $headerlen += 2;
        }

        // GZIP FOOTER - These be negative due to PHP's limitations
        $datacrc = unpack("V", substr($data, -8, 4));
        $datacrc = $datacrc[1];
        $isize = unpack("V", substr($data, -4));
        $isize = $isize[1];

        // Perform the decompression:
        $bodylen = $len - $headerlen - 8;
        if ($bodylen < 1) {
            // This should never happen - IMPLEMENTATION BUG!
            return null;
        }
        $body = substr($data, $headerlen, $bodylen);
        $data = "";
        if ($bodylen > 0) {
            switch ($method) {
                case 8:
                    // Currently the only supported compression method:
                    $data = gzinflate($body);
                    break;
                default:
                    // Unknown compression method
                    return false;
            }
        } else {
            // I'm not sure if zero-byte body content is allowed.
            // Allow it for now...  Do nothing...
        }

        // Verifiy decompressed size and CRC32:
        // NOTE: This may fail with large data sizes depending on how
        //       PHP's integer limitations affect strlen() since $isize
        //       may be negative for large sizes.
        if ($isize != strlen($data) || crc32($data) != $datacrc) {
            // Bad format!  Length or CRC doesn't match!
            return false;
        }

        return $data;
    }
}

?>