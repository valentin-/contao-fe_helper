var activeArticle;
var rt;

jQuery(document).ready(function($){

  rt = $('#fe_helper').data('token');
  // setTimeout(function(){
  //   if($('.rsfh-toolbar').length) {
  //     $('#fe_helper').css({
  //       left: $('.rsfh-toolbar').width() + 7
  //     })
  //   }
  // },100);

  $(document).on('mouseenter', '#fe_helper', function(){
    if($('.rsfh-toolbar').length) {
      $('#fe_helper').css({
        left: $('.rsfh-toolbar').width() + 7 
      })
    }
  })

  $(document).on('mouseenter', '#fe_helper ul.fe_helper_pages:first li', function(){
    if($(this).parents('ul').hasClass('fe_helper_articles')) {
      el = $(this).find('>a');
      column = el.data('column');
      index = el.data('article-index') - 1;
      if(column) {
        activeArticle = $('#'+column+' .mod_article').eq(index);
        activeArticle.addClass('fe_helper_highlight');
      }
    }
    // if($(this).closest('ul').hasClass('fe_helper_contents')) {
    //   el = $(this).find('>a');
    //   index = el.parent().index();
    //   console.log(index);
    //   activeArticle.find('*').eq(index).addClass('fe_helper_highlight');
    // }

  }) 

  $('.changeLayout').click(function(e) {
    e.preventDefault();

    $.ajax({
      url: window.location,
      type: 'POST',
      data: {
        feHelperAjax: true,
        action: 'changeLayout',
        id: $(this).data('id'),
        REQUEST_TOKEN: rt
      },
    })
    .done(function(data) {

      data = $.parseJSON(data);

      if(data.reload) {
        window.location.reload();
      }

    })
    
  });

  $(document).on('mouseleave', '#fe_helper .fe_helper_articles > li', function(){
    $('.mod_article').removeClass('fe_helper_highlight');
    // $('.mod_article *').removeClass('fe_helper_highlight');
  })

})

function fe_helper_select(el) {
  if(el.value) {
    window.open(el.value, '_blank');
  }
}