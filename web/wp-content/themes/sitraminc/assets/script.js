;// JavaScript Document
$(function () {

  var inWrap = $('.inner-wrapper'),
    $slide = $('.slide');


  function slideNext() {

    inWrap.animate({
      left: '-200%'
    }, 200, function () {

      inWrap.css('left', '-100%');

      $('.slide').last().after($('.slide').first());

    });

  }


  //Enabling auto scroll
  sliderInterval = setInterval(slideNext, 4000);


  $('.prev').on('click', function () {

    inWrap.animate({
      left: '0%'
    }, 200, function () {

      inWrap.css('left', '-100%');

      $('.slide').first().before($('.slide').last());

    });
  });


  $('.next').on('click', function () {

    clearInterval(sliderInterval);

    slideNext();

  });


});


