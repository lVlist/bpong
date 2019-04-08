$(function(){
    
    //Живой поиск
    $('.team').bind("change keyup input click", function() {
        if(this.value.length >= 1){
            $.ajax({
                type: 'post',
                url: "../func/search_hal9va.php", //Путь к обработчику
                data: {'referal':this.value,},
                response: 'text',
                success: function(data){
                    $(".search_hal9va").html(data).fadeIn(); //Выводим полученые данные в списке
                    
                }
            })
        }
    })
    
    $(".search_hal9va").hover(function(){
        $(".team").blur(); //Убираем фокус с input
    })

})