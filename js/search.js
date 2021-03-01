$(function(){
    
    //Добавление команды в турнир
    $('.team').bind("change keyup input click", function() {
        if(this.value.length >= 1){
            var id_game = $('#game').val();
            $.ajax({
                type: 'post',
                url: "../func/search.php", //Путь к обработчику
                data: {'add_team':this.value, 'id_game':id_game},
                response: 'text',
                success: function(data){
                    $(".search_create").html(data).fadeIn(); //Выводим полученые данные в списке
                    
                }
            });
        }
    });
    
    $(".search_create").hover(function(){
        $(".team").blur(); //Убираем фокус с input
    })

    //Замена команды в турнире
    $('.change_team').bind("change keyup input click", function() {
        if(this.value.length >= 1){
            var id_team = $('#team').val();
            var id_game = $('#game').val();
            $.ajax({
                type: 'post',
                url: "../func/search.php", //Путь к обработчику
                data: {'change_team':this.value,'id_team':id_team, 'id_game':id_game},
                response: 'text',
                success: function(data){
                    $(".search_change_team").html(data).fadeIn(); //Выводим полученые данные в списке
                    
                }
            })
        }
    })
    
    $(".search_change_team").hover(function(){
        $(".change_team").blur(); //Убираем фокус с input
    })

    //Изменение название команды или удаление
    $('.edit_team').bind("change keyup input click", function() {
        if(this.value.length >= 1){
            $.ajax({
                type: 'post',
                url: "../func/search.php", //Путь к обработчику
                data: {'edit_team':this.value},
                response: 'text',
                success: function(data){
                    $(".search_teams").html(data).fadeIn(); //Выводим полученые данные в списке
                    
                }
            })
        }
    })
    
    $(".search_teams").hover(function(){
        $(".edit_team").blur(); //Убираем фокус с input
    })

    //Изменение название команды или удаление
    $('.grand').bind("change keyup input click", function() {
        if(this.value.length >= 1){
            var id_game = $('#game').val();
            var type = $('#type').val();
            var pos = $('#pos').val();
            $.ajax({
                type: 'post',
                url: "../func/search.php", //Путь к обработчику
                data: {'add_grand':this.value, 'id_game':id_game, 'type':type, 'pos':pos},
                response: 'text',
                success: function(data){
                    $(".search_grand").html(data).fadeIn(); //Выводим полученые данные в списке
                    
                }
            })
        }
    })
    
    $(".search_grand").hover(function(){
        $(".grand").blur(); //Убираем фокус с input
    })

})



function addInput() {
    var x  = document.getElementById("profile").childElementCount;

    if (x < 5) {
        var profile = document.getElementById('profile');
        var div = document.createElement('div');
        div.id = 'input' + ++x;
        div.innerHTML = '<input type="number" autocomplete="off" class="form-grand" name="is1[]">';
        profile.appendChild(div);

        var profile = document.getElementById('profile2');
        var div = document.createElement('div');
        div.id = 'input' + x;
        div.innerHTML = '<input type="number" autocomplete="off" class="form-grand" name="is2[]">';
        profile.appendChild(div);
    }else{
        alert('Лимит исчерпан');
    }
}

function delInput() {
    var x = document.getElementById("profile").childElementCount;

    var id = ['del_input', document.getElementById('input' + x).dataset.id];

    if (document.getElementById('input' + x).dataset.id) {
        fetch('../func/edit.php', {
            method: 'POST',
            body: JSON.stringify(id)
        })
    }

    var div = document.getElementById('input' + x);
    div.remove();
    var div = document.getElementById('input' + x);
    div.remove();
    --x;

    if (x == 0) {
        x = 1;
        var profile = document.getElementById('profile');
        var div = document.createElement('div');
        div.id = 'input' + x;
        div.innerHTML = '<input type="number" autocomplete="off" class="form-grand" name="is1[]">';
        profile.appendChild(div);

        var profile = document.getElementById('profile2');
        var div = document.createElement('div');
        div.id = 'input' + x;
        div.innerHTML = '<input type="number" autocomplete="off" class="form-grand" name="is2[]">';
        profile.appendChild(div);

    }
}