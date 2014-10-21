(function($){
    $(document).ready(function() {
        
        $('.wordcamp_like').css('cursor', 'pointer').click(function() {
            var post_id = $(this).data('post_id');
            $('#wordcamp_like_'+post_id).html('Processando...').load(
                wordcamp_like.ajaxurl, 
                {
                    action: 'wordcamp_like', 
                    post_id: post_id
                }
            );
        });
        
    });
})(jQuery);
