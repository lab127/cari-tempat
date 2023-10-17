<?php

include plugin_dir_path(__FILE__) . 'http-service.php';

class PlacesData extends HttpService
{
    /**
     * Ambil review dari api
     * return sebagail string html
     */
    function reviews($reviews)
    {
        $reviews_html = "<div>";

        foreach ($reviews as $review) {
            $reviewer_name = $review->author_name;
            $reviewer_photo = $review->profile_photo_url;
            $reviewer_said = $review->text;
            $review_div = "
                <div style=\"display:flex;\">
                    <div style=\"max-width:20%;padding-top: 21px;padding-right: 20px;\">
                        <img style=\"border-radius:50% max-width:50px; max-height: 50px;\" src=\"{$reviewer_photo}\" />
                    </div>
                    <div style=\"max-width:80%\">
                        <p><strong>{$reviewer_name}</strong></p>
                        <div style=\"color:#646161; font-size:medium;\">{$reviewer_said}</div>
                    </div>
                </div>";
            $reviews_html .= $review_div;
        }
        $reviews_html .= "</div>";

        return $reviews_html;
    }

    /**
     * detail tempat2 dari keyword result google map
     * return content sebagai html string dalam array property
     * return image: photo ref dari api, untuk di download
     */
    function details($keywords)
    {
        $places_text_search = $this->get_places_text_search($keywords);
        $place_data = "";
        $photos = [];
        foreach ($places_text_search as $place) {
            $title = $place["name"];
            $slug = sanitize_title($title);

            $photo = [];
            $photo["ref"] = $place["photo"];
            $photo["name"] = $slug;
            $photos[] = $photo;

            $img_local_url = wp_upload_dir()["url"] . "/{$slug}.jpg";
            $address = $place["formatted_address"];
            $place_id = $place["place_id"];

            $place_detail = $this->get_place_detail($place_id);

            $phone = isset($place_detail->formatted_phone_number) ? $place_detail->formatted_phone_number : "";

            $place_data .= "<h3>{$title}</h3>";
            $place_data .= "<p><img style=\"max-width: 100%; height: auto;\" src=\"{$img_local_url}\" alt=\"{$title}\" /></p>";
            $place_data .= "<p>{$address}</p>";
            $place_data .= "<p>{$phone}</p>";

            $open_hours = "";
            if (isset($place_detail->opening_hours)) {
                $open_hours .= "<strong>Jam Buka</strong>";
                $open_hours .= "<ul>";
                foreach ($place_detail->opening_hours->weekday_text as $opening_hour) {
                    $open_hours .= "<li>" . $opening_hour . "</li>";
                }
                $open_hours .= "</ul>";
            } else {
                $open_hours = "";
            }

            $place_data .= $open_hours;
            $place_data .= isset($place_detail->reviews) ? $this->reviews($place_detail->reviews) : "";
        }
        return array(
            "content" => $place_data,
            "image" => $photos
        );
    }

    /**
     * download photo dari API kemudian tambahkan di media library 
     */
    function photo_to_media($post_id, $ref, $name)
    {
        $upload = wp_upload_dir();
        if (wp_mkdir_p($upload['path'])) {
            $upload_dir = $upload['path'] . '/' . $name;
        } else {
            $upload_dir = $upload['basedir'] . '/' . $name;
        }

        $this->get_photo($ref, $upload_dir);

        $wp_filetype = wp_check_filetype($name, null);

        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($name),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $upload_dir, $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $upload_dir);
        wp_update_attachment_metadata($attach_id, $attach_data);

        return $attach_id;
    }

    /**
     * download image hasil result search keyword
     * masukan ke database sebagai attachment
     */
    function images($post_id, $photos)
    {

        $attahments_id = [];
        foreach ($photos as $photo) {
            $image_ref = $photo["ref"];
            $image_name = $photo["name"] . ".jpg";

            $attahments_id[] = $this->photo_to_media($post_id, $image_ref, $image_name);
        }

        return $attahments_id;
    }

    /**
     * dari semua data yang diambil masukan sebagai post
     * $post_content perlu convert
     * karena ada berapa kasus tidak bisa masuk ke db
     * hasil convert ada karakter aneh
     */
    function post($keyword)
    {
        $post_title = ucwords($keyword);
        $post_excerpt = "<p>Berikut adalah {$post_title} yang bisa Anda kunjungi lengkap dengan review jujur dari para pembeli.</p>";

        $post_data = $this->details($post_title);

        $post_content = $post_excerpt . $post_data["content"];

        $post_content = iconv('ISO-8859-1', 'UTF-8', $post_content);

        $post = array(
            'post_content' => $post_content,
            'post_excerpt' => $post_excerpt,
            'post_status'  => 'draft',
            'post_title'   => $post_title,
            'post_parent'  => '',
            'post_type'    => 'post'
        );

        $post_id = wp_insert_post($post, true);

        if (!$post_id) {
            return false;
        }


        $this->images($post_id, $this->details($post_title)["image"]);

        return $post_id;
    }
}
