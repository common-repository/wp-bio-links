(function( $ ) {

    var wrapper = $("#wp_bio_links_items"); //Fields wrapper
    var add_link = $("#wp_bio_links_add_link"); //Add link
    var upload_thumbnail = $("#wp_bio_links_upload_thumbnail"); //Upload_thumbnail
    var remove_thumbnail = $("#wp_bio_links_remove_thumbnail"); //Remove_thumbnail
    var x = $("#wp_bio_links_items .postbox").length; //initial text box count

    $(add_link).click(function(e){ //on add input button click
        e.preventDefault();
        x++; //text box increment
        $(wrapper).append('<div class="postbox"><h2 style="background-color:#e9e9e9;">Link</h2><div class="inside"><p>                <label>Link text</label><br /><input name="wp_bio_links[items][' + x + '][text]" type="text" value="" /><br /></p><p><label>Link url</label><br /><input name="wp_bio_links[items][' + x + '][url]" type="text" value="" /><br /></p><p> <button type="button" class="wp_bio_links_item_move_down">Move Down Button</button> <button type="button" class="wp_bio_links_item_move_up">Move Up Button</button> <button type="button" class="wp_bio_links_item_remove">Remove Button</button> </p></div></div>'); //add input box

    });
        
    $(wrapper).on("click",".wp_bio_links_item_remove", function(e){ //user click on remove element
        e.preventDefault(); 
        $(this).parent('p').parent('div').parent('div').remove(); 
        x--;
    });

    $(wrapper).on("click",".wp_bio_links_item_move_up", function(e){ //user click on move up element
        e.preventDefault(); 
        var current = $(this).parent('p').parent('div').parent('div');
        current.prev().before(current);
    });

    $(wrapper).on("click",".wp_bio_links_item_move_down", function(e){ //user click on move down element
        e.preventDefault(); 
        var current = $(this).parent('p').parent('div').parent('div');
        current.next().after(current);
    });

    $(wrapper).on("click",".wp_bio_links_item_move_down, .wp_bio_links_item_move_up, .wp_bio_links_item_remove", function(e){ //user click on move down element
        e.preventDefault(); 

        $('.postbox', '#wp_bio_links_items').each(function(i, fields){
            // Loop over data-fields groups and grab their index
            $('input', fields).each(function(){
                $(this).attr('name', $(this).attr('name').replace(/wp_bio_links\[items\]\[[^\]]*\]/, 'wp_bio_links[items]['+(i+1)+']')); // replace with definitive name
            });
        });
    });

    // The "Upload thumbnail" button
    $(upload_thumbnail).click(function() {
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var button = $(this);
        wp.media.editor.send.attachment = function(props, attachment) {
            $(button).parent().prev().attr('src', attachment.url);
            $(button).prev().val(attachment.url);
            wp.media.editor.send.attachment = send_attachment_bkp;
        }

        wp.media.editor.open(button);
        return false;
    });

    // The "Remove thumbnail" button (remove the value from input type='hidden')
    $(remove_thumbnail).click(function() {
        var answer = confirm('Are you sure?');
        if (answer == true) {
            var src = $(this).parent().prev().attr('data-src');
            $(this).parent().prev().attr('src', src);
            $(this).prev().prev().val('');
        }
        return false;
    });

    // Add color picker
    $( '.wp_bio_links_color_picker' ).wpColorPicker({palettes:false});

})( jQuery );

