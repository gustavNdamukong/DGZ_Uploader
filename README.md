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
            OR
            $upload = new \DGZ_Uploader\DGZ_Uploader('gallery');
        ```

There are only two parts to this package class you need to worry about; the constructor and the move() method:
       		-i) The DGZ_Uploader($path, $uniqueSubfolder = '')
        	-ii) The move($modify = 'original', $overwrite = false)

	This constructor takes two arguments
		a) a string $path which is the key of the upload destination folder as you have setup in your config file (settings.php). You are
		    telling it where to upload your file(s) to.This could be 'gallery', 'default' etc.
		b) Optionally, you can pass it a second argument which is a subfolder. This could be handy for example in e-commerce applications
		    where the images of a unique listed item have a folder of the item name, or it could be a specific album in an image gallery.
		    In that case just call the uploader like so:

			DGZ_Uploader('gallery', albumName);

    The move() method does the uploading, and it is where you specify whether to resize your images and whether to rename or overwrite duplicate copies
        of the images. It therefore takes two arguments;
        a) An optional string $modify which can contain one of three values;
            i) 'original': whether to upload the file as it is ('original'),
            ii) 'original-allow': whether to upload the file as is and perform no size or file type checks,
            iii) 'resize': whether to upload the file and resize it.

            By default, $modify is set to 'original'. So, this DGZ_Uploader package is designed to not resize your images unless you tell it to, though
            it will check that the file conforms to the specified file size and MIME type allowed by the application.

        b) An optional Boolean $overwrite which specifies whether to delete a previous copy of the same file if one is found, or rename the new one and
            keep both files.

## HERE IS WHAT IS POSSIBLE WITH THIS UPLOADING CLASS
## -When calling the DGZ_Uploader() (constructor), you MUST pass as an argument; the destination folder ($path) for the file(s) to be uploaded.
        This argument is a string which represents the key of the upload destination specified in the config file at config\dgz_uploader.php
        I have provided you with a starting upload destination which you can change

            'default' => 'images/store_imgs/',

        For example, if you want to set a separate upload destination for your image gallery, you can set that in your config file (config\dgz_uploader.php)
        like so (the trailing slash at the end is very important):

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


## -What types of files can you upload to your application
            At the moment, the DGZ_Uploader allows you to upload the following image file types:

            image/gif,
            image/jpeg,
            image/jpg,
            image/png;

       But you can upload other file types like videos, and audio files etc. Just remember to bypass the check for allowed file types as mentioned
       above by passing 'original-allow' to the move() when you call it to upload the file. For example, to upload a video file:

            ```php
                $upload = new DGZ_Uploader('videoUploadDir', $genreFolder);
                $upload->move('original-allow');
            ```

## -How you create a config file in app\config to mirror the config file from the package in order to modify and override its config settings

                ```bash
                    php artisan vendor:publish
                ```
            This will create the config file 'dgz_uploader.php' in app\config and there you will find clear guidance notes on how to modify the
              file upload settings. There are only two options you can set in the configuration file:

                i) The size that resized images will be resized to (if you choose to resize upon upload).
                ii) The upload destination directory path.



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

        ```php
            $uploader = new \DGZ_Uploader\DGZ_Uploader('default');
            $singleUploaded file = $uploader->getFilenames()[0];
        ```

## -Find out what went wrong if the upload process stalls
    If something went wrong in the upload process and you file was not uploaded, it may fail silently leaving you with no clue as to what went wrong.
    Fortunately, this system stores useful error messages in a messages array which you can access to have complete clarity on anything that can go wrong.

    The recommended way to use this program is to check first if the file was uploaded and if not, retrieve the in-built error messages like so:

        ```php
            //check if uploading was successful
            if ($upload->getFilenames()[0])
            {
                $newFilename = $upload->getFilenames()[0];
            }
            else
            {
                //redirect with error
                return Redirect::back()->withErrors(['Error', $upload->getMessages()]);
            }
        ```


