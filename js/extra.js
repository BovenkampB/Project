        window.varCount = 2;
        
        //add a new element
        $('#addVar').on('click', function(){
            window.varCount++;
            node = '<tr><td><input class="input" type="text" name="telefoon' + window.varCount + '" id="var' + window.varCount + '" placeholder="Telefoon nr."></td></tr>';
            $(this).parent().append(node);
        });
        
        //remove last element
        $('#remVar').on('click', function(){          
            if(window.varCount > 2) {
                window.varCount--;
                $(this).parent().children().last().remove();
            }
        });
