![issues](https://img.shields.io/github/issues/gustavNdamukong/DGZ_Uploader)
![forks](https://img.shields.io/github/forks/gustavNdamukong/DGZ_Uploader)
![stars](https://img.shields.io/github/stars/gustavNdamukong/DGZ_Uploader)
![license](https://img.shields.io/github/license/gustavNdamukong/DGZ_Uploader)

## UPLOAD AND RESIZE IMAGES IN LARAVEL HOWEVER YOU PLEASE.

Get this package by typing the following command in your terminal from the target application directory

        composer require dgz_uploader/dgz_uploader

Uploading is done by calling the constructor of the DGZ_Uploader() like so:

        ```php
            $upload = new \DGZ_Uploader\DGZ_Uploader('default');
        ```

There are only two parts to this package class you need to worry about; the constructor and the move() method:
        i) The DGZ_Uploader($path)
        ii) The move($modify = 'original', $overwrite = false)

    This constructor takes a string of the upload destination folder ($path). This is all it needs; you call it and tell it where to upload your file(s) to.
    The move() method does the uploading, and it is where you specify whether to resize your images and whether to rename or overwrite duplicate copies
        of the images. It therefore takes two arguments;
        a) An optional string $modify which specifies whether to upload the image as it is ('original') or to resize it ('resize'). By default, this is
            set to 'original'. So, this DGZ_Uploader package is designed to not resize your images unless you tell it to.
        b) An optional Boolean $overwrite which specifies whether to delete a previous copy of the same file if one is found, or rename the new one and
            keep both files.

## HERE IS WHAT IS POSSIBLE WITH THIS UPLOADING CLASS
## -When calling the DGZ_Uploader() (constructor), you MUST pass as an argument; the destination folder ($path) for the file(s) to be uploaded.
        This argument is a string which represents the key of the upload destination specified in the config file at config\dgz_uploader.php
        I have provided you with a starting upload destination which you can change

            'default' => 'images/store_imgs/',

        For example, if you want to set a separate upload destination for your image gallery, you can set that in your config file (config\dgz_uploader.php)
        like so:

            'gallery' => 'images/gallery/'

        Then upload to it like so:

            $upload = new \DGZ_Uploader\DGZ_Uploader('gallery');

        See the notes in this config file for guidance on how it all works. It's super easy :)


## -You can choose to upload your image and have it saved in its original form (not resized) to your specified upload destination directory.


            ```php
                $upload = new \DGZ_Uploader\DGZ_Uploader('default');

                $upload->move('original');
                OR
                $upload->move();
            ```

## -You can specify the maximum file size allowed in the config file. I have started you off with the size
     10240000000 KB that I like to use myself, but you can always change it:

           ```php
                'maxFileUploadSize' => 10240000000
           ```


## -You can upload  a file, and have it resized and renamed if a file of the same name previously exists in the destination folder:

            ```php
                $upload = new \DGZ_Uploader\DGZ_Uploader('default');
                $upload->move('resize');
            ```


        Again, as mentioned above about the move() method; if the first argument $modify is given the string 'resize', your image(s) will be resized
            by the max upload size you have set for your application in the config file (config\dgz_uploader.php)
        Optionally, you can pass a second argument true to overwrite previous copies of the same image at the upload destination.
        Both $modify and $overwrite arguments are by default of the values 'resize' and false respectively.
        This means that even if you do not pass any arguments to move(), newly uploaded files will be resized, and duplicate files in the upload
            destination directory will be renamed and both copies will be kept.

## -You can upload a file in its original size but make it overwrite previous copies:

            ```php
                $upload = new \DGZ_Uploader\DGZ_Uploader('default');
                $upload->move('original', 'true');
            ```


## -You can upload a file in its original size and rename duplicate copies (not overwrite):

             ```php
                $upload = new \DGZ_Uploader\DGZ_Uploader('default');

                $upload->move('original');
                OR
                $upload->move('original', 'false');
            ```

## -How you create a config file in app\config to mirror the config file from the package in order to modify and override its config settings

                ```bash
                    php artisan vendor:publish
                ```
            This will create the config file 'dgz_uploader.php' in app\config and there you will find clear guidance notes on how to modify the
              file upload settings like set the maximum file size allowed, and the upload folder destination.



## RETRIEVE UPLOADED FILE(S)

## -To get uploaded files:
        use getFilenames() which returns an array of all uploaded files.

## -To get the names of multiple files uploaded, loop through this array like so:

         ```php
            $uploader = new \DGZ_Uploader\DGZ_Uploader('default');
            $upload->move();
            $uploadedFiles = $uploader->getFilenames();
            foreach ($uploadedFiles as $file)
            {
                echo $file;
            }
        ```


## -To get the name of a single file uploaded-if you only uploaded one file, just grab the file at the first index e.g.

        ````php
            $uploader = new \DGZ_Uploader\DGZ_Uploader('default');
            $singleUploaded file = $uploader->getFilenames()[0];
        ```



