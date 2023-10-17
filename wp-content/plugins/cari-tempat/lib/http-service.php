<?php
class HttpService
{
    private $endpoint;
    private $params;
    private $api_key;

    function __construct($api_key)
    {
        $country = "id";
        $this->api_key = $api_key;
        $this->endpoint = "https://maps.googleapis.com/maps/api/place";
        $this->params = "/json?key={$this->api_key}&region={$country}&language={$country}";
    }

    function get_data($url)
    {
        $options = array(
            'http' => array(
                'method' => "GET",
                'header' => "Accept-language: id,ID\r\n"
            )
        );

        $context = stream_context_create($options);
        $res = file_get_contents($url, false, $context);
        return $res;
    }

    function get_places_text_search($keyword)
    {
        $keyword = str_replace(" ", "+", $keyword);
        $url = "{$this->endpoint}/textsearch{$this->params}&query={$keyword}";

        $res = $this->get_data($url);
        $results = json_decode($res)->results;
        $places_id = [];
        $data = [];
        foreach ($results as $result) {
            $place = [];
            $place["place_id"] = $result->place_id;
            $place["photo"] = isset($result->photos) ? $result->photos[0]->photo_reference : "";
            $place["formatted_address"] = $result->formatted_address;
            $place["name"] = $result->name;
            $place["rating"] = $result->rating;
            $data[] = $place;
        }

        return $data;
    }

    function get_photo($photo_ref, $photo_path, $max_width = "1000", $max_height = "1000")
    {
        $url = "{$this->endpoint}/photo?key={$this->api_key}&photoreference={$photo_ref}&sensor=false&maxheight={$max_height}&maxwidth={$max_width}";

        $photo_data = file_get_contents($url);

        file_put_contents($photo_path, $photo_data);
    }

    function get_place_detail($place_id)
    {
        $url = "{$this->endpoint}/details{$this->params}&placeid={$place_id}";

        $res = $this->get_data($url);
        $results = json_decode($res)->result;

        return $results;
    }
}
