$(function(){
    
    //Живой поиск
    $('.team').bind("change keyup input click", function() {
        if(this.value.length >= 1){
            $.ajax({
                type: 'post',
                url: "../func/search_edit_team.php", //Путь к обработчику
                data: {'referal':this.value,},
                response: 'text',
                success: function(data){
                    $(".search_edit_team").html(data).fadeIn(); //Выводим полученые данные в списке
                    
                }
            })
        }
    })
    
    $(".search_edit_team").hover(function(){
        $(".team").blur(); //Убираем фокус с input
    })

})