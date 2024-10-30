<?php

namespace Kr_page_details\Katorymnd_reaction;

class KatorymndUtility
{
    /**
     * Gets the ID of the current page.
     *
     * @return int|null The ID of the current page or null if not found.
     */
    public static function getCurrentPageId()
    {
        global $post;

        if (in_the_loop()) {
            return get_the_ID();
        } elseif (is_a($post, 'WP_Post')) {
            return $post->ID;
        } else {
            return null;
        }
    }

    /**
     * Gets details of the current page such as ID, slug, and title.
     *
     * @return array An associative array containing the 'id', 'slug', and 'title' of the current page.
     */
    public static function get_kr_current_page_details()
    {
        // Utilize the getCurrentPageId method
        $pageId = self::getCurrentPageId();

        $details = [
            'id'    => $pageId,
            'slug'  => null,
            'title' => null
        ];

        // If a page ID is available, populate the slug and title
        if ($pageId !== null) {
            $details['slug'] = get_post_field('post_name', $pageId);
            $details['title'] = get_the_title($pageId);
        }

        return $details;
    }
}
