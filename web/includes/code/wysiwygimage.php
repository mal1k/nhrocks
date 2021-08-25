<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* Getting Symfony Containers */
    $container = SymfonyCore::getContainer();

    /* Getting wysiwyg and translator services */
    $imageUploader = $container->get('imageuploader');

    $return = [];

    if ($_FILES['favicon_file']['name']) {
        /* FavIcon */
        $return = $imageUploader->saveFavIcon($_FILES['favicon_file']);
    } elseif ($_FILES['header_image']['name']) {
        /* Site Logo */
        $return = $imageUploader->saveLogo($_FILES['header_image']);
    } elseif ($_FILES['background_image']['name']) {
        /* Background Image */
        $return = $imageUploader->saveBackgroundImage($_FILES['background_image'], 'background_image');
    } elseif ($_FILES['background_image_generic']['name']) {
        /* Generic Background Image */
        $imageSize = getimagesize($_FILES['background_image_generic']['tmp_name']);
        $return = $imageUploader->saveContentImages($_FILES['background_image_generic'], $imageSize[0], $imageSize[1], $_GET['domain_id']);
    } elseif ($_FILES['slideImage']['name']) {
        /* Slider Upload */
        $return = $imageUploader->saveContentImages($_FILES['slideImage'], IMAGE_SLIDER_WIDTH, IMAGE_SLIDER_HEIGHT, $_GET['domain_id']);
    } elseif ($_POST['unsplash']) {
        $return = $imageUploader->saveSliderImagesUnsplash($_POST['unsplash']);
    }

    echo json_encode($return);
}
