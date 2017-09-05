$(function(){
   
    console.log('jQuery has started.');
    
    var $gallery = $('.gallery').masonry({
        columnWidth : 400,      //width of columns
        itemSelector : 'li',    //selector for individual gallery items
        gutter : 20,            //gap between columns
        fitWidth : true         //makes centering gallery possible
    });
    
    $gallery.imagesLoaded().progress( function(){
       $gallery.masonry('layout'); 
    });
    
});