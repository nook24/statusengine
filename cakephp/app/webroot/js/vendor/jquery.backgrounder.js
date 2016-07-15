/*
Copyright (c) 2010 Erik Dungan, http://bigfolio.com/

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
(function($) {
  $.fn.backgrounder = function(options) {
    var defaults = { element : 'body' };
    var options = $.extend(defaults, options);
    // Get the image we're using
    var img = $(this).children('img').first();
    // Get the image source
    var src = $(img).attr('src');
    // Hide the original element
    $(this).hide();
    // Make parent relative
    w = options.element == 'body' ? $(window).width() : $(options.element).width();
    h = options.element == 'body' ? $(window).height() : $(options.element).height();
    // Create a new div
    $('<div id="backgrounder-container"></div>')
      .css({'position':'absolute','z-index':-100,'left':0,'top':0,'overflow':'hidden','width':w,'height':h})
      .appendTo($(options.element))
    // Create a new image
    $('<img />')
      .appendTo($('#backgrounder-container'))
      .attr('src',src)
      .css({'position':'absolute'})
      .hide()
      .load(function() {
        resizeBackgrounder(this, options.element);
        $(this).fadeIn();
      })
    // Resize handler
    $(window).resize(function() {
          var newW = options.element == 'body' ? $(window).width() : $(options.element).width();
        var newH = options.element == 'body' ? $(window).height() : $(options.element).height();
        $('#backgrounder-container').css({'width':newW,'height':newH});
          resizeBackgrounder('#backgrounder-container img:first', options.element);
    })
    // Update function
    function resizeBackgrounder(item, elem) {
      //console.log("Background resized ... ");
      if (elem != 'body') {
          w = $(elem).width();
          h = $(elem).height();
      } else {
          w = $(window).width();
          h = $(window).height();
      }
      //console.log("Element Width: " + w);
      //console.log("Element Height: " + h);
      var ow = $(item).width();
      var oh = $(item).height();
      //console.log("Container Width: " + ow);
      //console.log("Container Height: " + oh);
      if (ow / oh > w / h) { // image aspect ratio is wider than browser window
        var scale = h / oh;
        $(item).attr({'width':ow * scale,'height':oh * scale});
      } else {
        var scale = w / ow;
        $(item).attr({'width':ow * scale,'height':oh * scale});
      }
      $(item).css({'left':-(($(item).width()-w)/2),'top':-(($(item).height()-h)/2)});
    }

    // Return
    return this.each(function() { });
  };
}) (jQuery);
