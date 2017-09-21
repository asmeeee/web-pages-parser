<?php

function parseText($url) {
    $text = file_get_contents($url);
    $text = filterTags(array(
        'script',
        'style'
    ), $text);

    // Multiple whitespaces
    $text = preg_replace("/\s+/", ' ', $text);

    // New lines
    //$text = preg_replace("/\r\n|\r|\n/", '<br>', $text);
	
	// Remove HTML tags
	$text = strip_tags($text);
	
	// Convert HTML codes into symbols
	$text = html_entity_decode($text);

    return $text;
}

function filterTags($tags = array(), $str) {
    foreach ($tags as $tag) {
        $str = preg_replace('/<' . $tag . '[^>]*>([\s\S]*?)<\/' . $tag . '[^>]*>/', '', $str);
    }

    return $str;
}