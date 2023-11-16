<?php
 
    function get_html($url) {
        $headers = array(
            "authority" => "www.xiaohongshu.com",
            "cache-control" => "max-age=0",
            "sec-ch-ua" => '"Chromium";v="21", " Not;A Brand";v="99"',
            "sec-ch-ua-mobile" => "?0",
            "sec-ch-ua-platform" => '"Windows"',
            "upgrade-insecure-requests" => "1",
            "user-agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36",
            "accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "sec-fetch-site" => "same-origin",
            "sec-fetch-mode" => "navigate",
            "sec-fetch-user" => "?1",
            "sec-fetch-dest" => "document",
            "accept-language" => "zh-CN,zh;q=0.9",
        );
     
        $options = array(
            'http' => array(
                'header' => implode("\r\n", array_map(
                    function ($v, $k) {
                        return $k . ':' . $v;
                    },
                    $headers,
                    array_keys($headers)
                )),
            ),
        );
     
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
     
        return $response;
    }
     
    function json_content($html) {
        $rule = '/<script>window\.__INITIAL_STATE__=(.*?)<\/script>/i';
        preg_match($rule, $html, $matches);
     
        if ($matches) {
            $content = $matches[1];
            return $content;
        } else {
            return null;
        }
    }
     
    function get_image_urls($url) {
        $html = get_html($url);
        $js = json_content($html);
        $js = str_replace("\\u002F", "/", $js);
     
        preg_match_all('/"url":"(http:\/\/[^":\{\}\[\]]*?wm_1)"/', $js, $all_urls);
     
        return array('image_urls' => $all_urls[1]);
    }
     
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $url = $_GET['url'];
        if ($url) {
            $result = get_image_urls($url);
            header('Content-Type: application/json');
            echo json_encode($result);
        } else {
            header('Content-Type: application/json');
            echo json_encode(array('error' => 'Missing URL parameter'));
        }
    }
     
?>
