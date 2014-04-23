<?php

/**
 * This helper generates the tag for embedding videos from youtube and vimeo,
 * Next features, integration with Redtube and megavideo. :D
 * 
 * @name       Video Helper
 * @author     Emerson Soares (dev.emerson@gmail.com)
 * @version    1.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php) 
 * 
 */

/*
 *
 * Load AppHelper
 *
 */ 
App::uses('AppHelper', 'View/Helper');

/**
* @property HtmlHelper $Html
* @property FormHelper $Form
* @property JsHelper $Js
*/

class VideoHelper extends AppHelper {

    /**
    * Helpers to load with this helper.
    */
    public $helpers = array('Html');

    /**
    * An array of videos API's this helper will use 
    * @access private
    */
    private $apis = array(
        'youtube_image' => 'http://i.ytimg.com/vi', // Location of youtube images 
        'youtube' => 'http://www.youtube.com', // Location of youtube player 
        'vimeo' => 'http://player.vimeo.com/video'
    );

    /**
    * Get Video Thumbnail
    * @param string $url video url to retriev thumbnail
    * @param string $size thumbnail size
    * @param string|array $options Html Link attributes e.g. array('width' => 250)
    *
    * @return string An `<img />` element
    * @access public
    */
    public function thumbnail($url, $size = 'thumb', $options = array()) { 
        if ($this->getVideoSource($url) == 'youtube') {
            return $this->youTubeThumbnail($url, $size, $options);
        } else if ($this->getVideoSource($url) == 'vimeo') {
           return $this->vimeoThumbnail($url, $size, $options);
        }
    }

    /**
    * Get Video ID
    * @param string $url video url to retriev thumbnail
    *
    * @return string An Video ID
    * @access public
    */
    public function getVideoId($url) {
        if ($this->getVideoSource($url) == 'youtube') {
            $params = $this->getUrlParams($url);
            return (isset($params['v']) ? $params['v'] : $url);
        } else if ($this->getVideoSource($url) == 'vimeo') {
            $path = parse_url($url, PHP_URL_PATH);
            return substr($path, 1);
        }
    }

    /**
    * Get Url Params
    * @param string $url video url to retriev thumbnail
    *
    * @return string parameter url
    * @access private
    */
    private function getUrlParams($url) {
        $query = parse_url($url, PHP_URL_QUERY);
        $queryParts = explode('&', $query);

        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

    /**
    * Get Video Source
    * @param string $url video url to retriev thumbnail
    *
    * @return string e.g: youtube or vimeo
    * @access private
    */
    private function getVideoSource($url) {
        $parsed_url = parse_url($url);

        $host = $parsed_url['host'];
        if (!$this->isip($host)) {
            if (!empty($host))
                $host = $this->returnDomain($host);
            else
                $host = $this->returnDomain($url);
        }
        $host = explode('.', $host);
        if (is_int(array_search('vimeo', $host)))
            return 'vimeo';
        elseif (is_int(array_search('youtube', $host)))
            return 'youtube';
        else
            return false;
    }

    /**
    * Check is host using IP Address
    * @param string $url video url to retriev thumbnail
    *
    * @return bool
    * @access private
    */
    private function isip($url) {
        //first of all the format of the ip address is matched 
        if (preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/", $url)) {
            //now all the intger values are separated 
            $parts = explode(".", $url);
            //now we need to check each part can range from 0-255 
            foreach ($parts as $ip_parts) {
                if (intval($ip_parts) > 255 || intval($ip_parts) < 0)
                    return false; //if number is not within range of 0-255 
            }
            return true;
        }
        else
            return false; //if format of ip address doesn't matches 
    }


    /**
    * Get host Domain
    * @param string $domainb host domain url
    *
    * @return string domain name
    * @access private
    */
    private function returnDomain($domainb) {
        $bits = explode('/', $domainb);
        if ($bits[0] == 'http:' || $bits[0] == 'https:') {
            $domainb = $bits[2];
        } else {
            $domainb = $bits[0];
        }
        unset($bits);
        $bits = explode('.', $domainb);
        $idz = count($bits);
        $idz-=3;
        if (strlen($bits[($idz + 2)]) == 2) {
            $url = $bits[$idz] . '.' . $bits[($idz + 1)] . '.' . $bits[($idz + 2)];
        } else if (strlen($bits[($idz + 2)]) == 0) {
            $url = $bits[($idz)] . '.' . $bits[($idz + 1)];
        } else {
            $url = $bits[($idz + 1)] . '.' . $bits[($idz + 2)];
        }
        return $url;
    }

   /**
    * Get Youtube Thumbnail
    * @param string $url video url to retriev thumbnail
    * @param string $size thumbnail size
    * @param string|array $options Html Link attributes e.g. array('width' => 250)
    *
    * @return string An `<img />` element
    * @access public
    */
    public function youTubeThumbnail($url, $size = 'thumb', $options = array()) {
        $video_id = $this->getVideoId($url);

        $accepted_sizes = array(
            'thumb' 	=> 'default', // 120px x 90px 
            'large' 	=> 0, // 480px x 360px 
            'thumb1' 	=> 1, // 120px x 90px at position 25% 
            'thumb2' 	=> 2, // 120px x 90px at position 50% 
            'thumb3' 	=> 3,  // 120px x 90px at position 75% 
            'hqthumb'	=> 'hqdefault'
        );
        $image_url = $this->apis['youtube_image'] . DS . $video_id . DS . $accepted_sizes[$size] . '.jpg';
        return $this->Html->image($image_url, $options);
    }

    /**
    * Get Vimeo Thumbnail
    * @param string $url video url to retriev thumbnail
    * @param string $size thumbnail size
    * @param string|array $options Html Link attributes e.g. array('width' => 250)
    *
    * @return string An `<img />` element
    * @access public
    */
    public function vimeoThumbnail($url, $size = 'thumb', $options = array()) {
        $id = $this->getVideoId($url);
        $data = file_get_contents("http://vimeo.com/api/v2/video/{$id}.json");
        $data = json_decode($data);

        switch($size) {
            case 'thumb' :
                $image_url = $data[0]->thumbnail_medium;
                break;
            case 'hqthumb' :
                $image_url = $data[0]->thumbnail_large;
                break;
            case 'smallthumb' :
                $image_url = $data[0]->thumbnail_small;
                break;
        }

        return $this->Html->image($image_url, $options);

    }

    /**
    * Set second to human times
    * @param string $sec how many second
    * @param bool $padHours, default false
    *
    * @return string video time lengh, e.g 30:05
    * @access public
    */
    public function sec2hms ($sec, $padHours = false) {

        // start with a blank string
        $hms = "";

        // do the hours first: there are 3600 seconds in an hour, so if we divide
        // the total number of seconds by 3600 and throw away the remainder, we're
        // left with the number of hours in those seconds
        $hours = intval(intval($sec) / 3600); 

        if(!empty($hours)) {
            // add hours to $hms (with a leading 0 if asked for)
            $hms .= ($padHours) 
                  ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
                  : $hours. ":";
        }
        // dividing the total seconds by 60 will give us the number of minutes
        // in total, but we're interested in *minutes past the hour* and to get
        // this, we have to divide by 60 again and then use the remainder
        $minutes = intval(($sec / 60) % 60); 

        // add minutes to $hms (with a leading 0 if needed)
        $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";

        // seconds past the minute are found by dividing the total number of seconds
        // by 60 and using the remainder
        $seconds = intval($sec % 60); 

        // add seconds to $hms (with a leading 0 if needed)
        $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

        // done!
        return $hms;

    }


    /**
    * Embed Video
    *
    * @param string $url video url
    * @param string|array $options Html Link attributes e.g. array('class' => 'video')
    *
    * @return string An `<iframe />` element tag
    * @access public
    */
    public function embed($url, $settings = array()) {

        if ($this->getVideoSource($url) == 'youtube') {
            return $this->youTubeEmbed($url, $settings);
        } elseif ($this->getVideoSource($url) == 'vimeo') {
            return $this->vimeoEmbed($url, $settings);
        } elseif (!$this->getVideoSource($url)) {
            return $this->Html->tag('notfound', __('Sorry, video does not exists'), array('type' => 'label', 'class' => 'error'));
        }
    }

    /**
    * Embed Youtube Video
    *
    * @param string $url video url
    * @param string|array $options Html Link attributes e.g. array('class' => 'video')
    *
    * @return string An `<iframe />` element tag
    * @access public
    */
    public function youTubeEmbed($url, $settings = array()) {

        $default_settings = array(
            'hd' => true, 
            'width' => 640,
            'height' => 390,
            'allowfullscreen' => 'true', 
            'frameborder' => 0
        );

        $settings = array_merge($default_settings, $settings);
        $video_id = $this->getVideoId($url);
        $settings['src'] = $this->apis['youtube'] . DS . 'embed' . DS . $video_id . '?autohide=0&hd=' . $settings['hd'];

        return $this->Html->tag('iframe', null, array(
                    'width' => $settings['width'],
                    'height' => $settings['height'],
                    'src' => $settings['src'],
                    'frameborder' => $settings['frameborder'],
                    'allowfullscreen' => $settings['allowfullscreen'])
                ) . $this->Html->tag('/iframe');
    }

    /**
    * Embed Vimeo Video
    *
    * @param string $url video url
    * @param string|array $options Html Link attributes e.g. array('class' => 'video')
    *
    * @return string An `<iframe />` element tag
    * @access public
    */
    public function vimeoEmbed($url, $settings = array()) {
        $default_settings = array
            (
            'width' => 640,
            'height' => 390,
            'show_title' => 1,
            'show_byline' => 1,
            'show_portrait' => 0,
            'color' => '00adef',
            'allowfullscreen' => 1,
            'autoplay' => 1,
            'loop' => 1,
            'frameborder' => 0
        );
        $settings = array_merge($default_settings, $settings);

        $video_id = $this->getVideoId($url);
        $settings['src'] = $this->apis['vimeo'] . DS . $video_id . '?title=' . $settings['show_title'] . '&amp;byline=' . $settings['show_byline'] . '&amp;portrait=' . $settings['show_portrait'] . '&amp;color=' . $settings['color'] . '&amp;autoplay=' . $settings['autoplay'] . '&amp;loop=' . $settings['loop'];
        return $this->Html->tag('iframe', null, array(
                    'src' => $settings['src'],
                    'width' => $settings['width'],
                    'height' => $settings['height'],
                    'frameborder' => $settings['frameborder'],
                    'webkitAllowFullScreen' => $settings['allowfullscreen'],
                    'mozallowfullscreen' => $settings['allowfullscreen'],
                    'allowFullScreen' => $settings['allowfullscreen']
                )) . $this->Html->tag('/iframe');
    }


    /**
    * Get video length
    *
    * @param string $url video url
    *
    * @return string length in second format
    * @access public
    */
    public function length($url) {

        if ($this->getVideoSource($url) == 'youtube') {
            return $this->youtubeLength($url);
        } else if ($this->getVideoSource($url) == 'vimeo') {
           return $this->vimeoLength($url);
        }
    }

    /**
    * Get Youtube length
    *
    * @param string $url video url
    *
    * @return string length in second format
    * @access public
    */
    public function youtubeLength($url) {

        // Sets the video ID 
        $video_id = $this->getVideoId($url); 

        // set video data feed URL
        $feedURL = 'http://gdata.youtube.com/feeds/api/videos/' . $video_id;

        // read feed into SimpleXML object
        $entry = simplexml_load_file($feedURL);

        // parse video entry
        $video = $this->parseVideoEntry($entry);

        $length = ($video->length);

        return $length;
    }


    /**
    * Get Vimeo length
    *
    * @param string $url video url
    *
    * @return string length in second format
    * @access public
    */
    public function vimeoLength($url) {
        $id = $this->getVideoId($url);
        $data = file_get_contents("http://vimeo.com/api/v2/video/$id.json");
        $data = json_decode($data);

        $length = $data[0]->duration;
        return $length;
    }


    /**
    * Parse video from youtube
    *
    * @param string $url video url
    *
    * @return string length in second format
    * @access public
    */
    public function parseVideoEntry($entry) {      
        $obj= new stdClass;

        // get nodes in media: namespace for media information
        $media = $entry->children('http://search.yahoo.com/mrss/');
        $obj->title = $media->group->title;
        $obj->description = $media->group->description;

        // get <yt:duration> node for video length
        $yt = $media->children('http://gdata.youtube.com/schemas/2007');
        $attrs = $yt->duration->attributes();
        $obj->length = $attrs['seconds']; 


        // return object to caller  
        return $obj;      
    }  

}
?> 

