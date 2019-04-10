$(function(){
    
    //Живой поиск
    $('.team').bind("change keyup input click", function() {
        if(this.value.length >= 1){
            var id_game = $('#game').val();
            $.ajax({
                type: 'post',
                url: "../func/search.php", //Путь к обработчику
                data: {'referal':this.value, 'id_game':id_game},
                response: 'text',
                success: function(data){
                    $(".search_create").html(data).fadeIn(); //Выводим полученые данные в списке
                    
                }
            })
        }
    })
    
    $(".search_create").hover(function(){
        $(".team").blur(); //Убираем фокус с input
    })

})