    [![Issues] (https://img.shields.io/github/issues/gustavNdamukong/DGZ_Uploader?style=flat-square)]

    ## Upload and resize images however you please.

    Get this package by typing the following command in your terminal from the target application directory

            composer require dgz_uploader/dgz_uploader

    Uploading is done by calling the constructor of the DGZ_Uploader() like so:

            ```php
                $upload = new \DGZ_Uploader\DGZ_Uploader('default');
            ```

    Here is what is possible with this uploading class:
        -You MUST pass as an argument, the destination location for the file(s) to be uploaded. This argument is a string which represent the key
         of the upload destination specified in the config file at config\dgz_uploader.php
         I have provided you with a starting upload destination which you may wish to change 'default' => 'images/store_imgs/',
         See this config file for guidance on how it all works. It's super easy :)

        -You can choose to upload your image and have it saved in its original form (dimensions) in your specified upload destination folder.

                $upload = new \DGZ_Uploader\DGZ_Uploader('default');


        -You can specify that the uploaded file be resized by the dimensions you specify in the config file. I have started you off with the size
         10240000000 KB that i like to use myself, but you can always change it:

               ```php
                    'maxFileUploadSize' => 10240000000
               ```


        -You can upload  a file, and have it resized and renamed if a file of the same name previously exists in the destination folder it like so:

                ```php
                    $upload = new \DGZ_Uploader\DGZ_Uploader('default');
                    $upload->move('resize');
                ```


            The 'resize' argument gets the image(s) resized by the max upload size you have set for your application in the config file
            (config\dgz_uploader.php)
            Optionally, you can pass a second argument true to overwrite previous copies of the same image at the upload destination
            (both first and second arguments are 'resize' and false by default respectively. This means newly uploaded files will be resized, and if
           	the uploaded file is of the same with a pre-existing file athe upload destination, the new file is renamed and both copies will be kept.

	    -You can upload a file or files and not resize it but make it overwrite previous copies. Do it like so:

		        ```php
		            $upload->move('original', 'true');
		        ```


	    -You can upload a file or files and not resize them, and not overwrite previous copies. Do it like so:

		         ```php
		            $upload->move('original');
		            OR
		            $upload->move('original', 'false');
		        ```

		-Here is how you create a config file in app\config to mirror the config file from the package in order to modify and override its config settings

                    ```bash
                        php artisan vendor:publish
                    ```
                    This will create the config file 'dgz_uploader.php' in app\config and there you will find clear guidance notes on how to modify the
                    file upload settings like set the maximum file size allowed, and the upload folder destination.


    ## RETRIEVE UPLOADED FILE(S)

	-To get uploaded files, use getFilenames() which returns an array of all uploaded files.

	-To get the names of multiple files uploaded, you can then loop through this array like so:

		     ```php
		        $uploader = new \DGZ_Uploader\DGZ_Uploader('default');
		        $upload->move();
		        $uploadedFiles = $uploader->getFilenames();
		        foreach ($uploadedFiles as $file)
		        {
			        echo $file;
		        }
		    ```


	-To get the name of a single file uploaded if you only uploaded one file, just grap the file at the first index e.g.

			````php
			    $uploader = new \DGZ_Uploader\DGZ_Uploader('default');
			    $singleUploaded file = $uploader->getFilenames()[0];
            ```

