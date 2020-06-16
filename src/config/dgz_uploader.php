<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Maximum file upload size
    |--------------------------------------------------------------------------
    |
    | The maximum file size accepted by your application
    |
    */

    'maxFileUploadSize' => 10240000000,


    /*
    |--------------------------------------------------------------------------
    | File upload destination
    |--------------------------------------------------------------------------
    |
    | Specify where uploaded files should go.
    | Let it be in this format:
    |
    |   'fileUploadPath' => 'images/store_imgs/',
    |
    | Take note of the trailing slash.
    | You can create as many of these for different file upload destination paths.
    | For example; one for gallery images (you just have to append the album ID to it in the calling script):
    |   'gallery' => 'images/gallery/',
    |
    | Another for user profiles (you just have to append the user's ID to it in the calling script):
    |   'userProfiles' => 'images/userProfiles/',
    |
    | Another for product ads etc
    |   'products' => 'images/productImgs/',
    |
    |
    | To upload an image, just pass to DGZ_Uploader() the path to upload to like so:
    |   DGZ_Uploader('gallery');
    |   or
    |   DGZ_Uploader('userProfiles');
    |   or
    |   DGZ_Uploader('products');
    |
    | For now, we give you 'default' path for general purpose uploading which uploads to a sub folder 'store_imgs' in the
    | Laravel public/images/store_imgs. Keep this 'default' key as the uploader needs one.
    | But feel free to change the value to your needs. You could perhaps just use 'images' so it goes to public/images.
    | Again, feel free to add other key-value pairs.
    |
    */

    'default' => 'images/store_imgs/',
];