$(function(){
    
    //Живой поиск
    $('.edit_team').bind("change keyup input click", function() {
        if(this.value.length >= 1){
            var id_team = $('#team').val();
            var id_game = $('#game').val();
            $.ajax({
                type: 'post',
                url: "../func/search_edit_team.php", //Путь к обработчику
                data: {'referal':this.value,'id_team':id_team, 'id_game':id_game},
                response: 'text',
                success: function(data){
                    $(".search_edit_team").html(data).fadeIn(); //Выводим полученые данные в списке
                    
                }
            })
        }
    })
    
    $(".search_edit_team").hover(function(){
        $(".edit_team").blur(); //Убираем фокус с input
    })

})