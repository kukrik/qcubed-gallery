# QCubed-4 GalleryManager Plugin


## QCubed v4 plugin created for GalleryManager


This QCubed plugin allows you to create one gallery and new albums, upload images, rename and delete files, and more. 
On the frontend, nanogallery2 https://nanogallery2.nanostudio.org/ JavaScript is used to display the images.

Here, the list of galleries option from NanoGallery2 is not used; instead, QCubed-4 itself handles this functionality. 
We can customize the list view as per our requirements.

If you want to use NanoGallery2 gallery effects and animations, I recommend referring to their official documentation at: 
https://nanogallery2.nanostudio.org/datasource.html. It contains detailed instructions and examples on how to use these 
effects and animations in your galleries.

![Image of kukrik](screenshot/gallery.jpg?raw=true)

If you have not previously installed QCubed Bootstrap and twitter bootstrap, run the following actions on the command 
line of your main installation directory by Composer:

```
    composer require twbs/bootstrap v3.3.7
```
and

```
    composer require kukrik/qcubed-gallery
    composer require kukrik/bootstrap
    composer require kukrik/select2
```

