<?php
/*
 * Esse é o código FrontEnd
 */

    /**
     * Senha gerada em algum momento pelo o usuário.
     */
    $senha = "batata";

    /*
     * Número de interações que o pbkdf2 fará enquanto gera o hash da senha (Servidor precisa conhecer essa informação)
     */
    $iterations = 1000;

    /*
     * Criando o nounce pode ser um hash complexo e gerado por você, pode ser qualquer coisa mas quanto maior mais complexo (Será enviado ao servidor)
     */
    $nonce = hash('sha512',  uniqid('', true));
    $nonce_array = str_split($nonce);

   /**
   * Regra para gerar o salt da senha (Tanto o Fronte quanto o backend precisam conhecer, para gerar o salt do mesmo jeito) use sua criatividade para criar a regra.
   */
    $array_regra_salt = array('10','1','30','12','40','5','50','22','1',(count($nonce_array)-1), (int)(count($nonce_array)-1 * 2 / 9 -49));
    foreach ($array_regra_salt as $value) {
        $salt .= $nonce_array[$value];
    }
    

    /**
     * Geralmente, a senha que voce "armazena", em memória no frontend, precisa ser armazenada de forma semelhante ao seu backend. Vamos supor que no seu bakend
     * tenha um ldap, por padrão eles pegam a senha (do jeito que ela tá) e aplica um sha256 nela. Se ela chegou em sha256 ele vai aplicar novamente mais um sha256. Então,
     * vamos já armazenar essa informação do jeito que encontra-se no ldap.
     */
    $hash_salvo_ldap = hash('sha256',hash('sha256',$senha));

    /**
     * Agora, vamos aplicar mais um sha256, só que vamos adicionar um salt no final do hash após dois sha256.
     */
    $hash_salteado = hash('sha256',$hash_salvo_ldap.$salt);
    
    /**
     * E antes de enviar essa informação vamos adicionar mais um hash do tipo pbkdf2 Tem as vantagens do SHA
     * salteado mas artificialmente implementa uma derivação lenta, que gera um custo exponencial adicional 
     * e coloca interrupções no cálculo matemático que atrasam o hashing de múltiplas amostras em lote.
     */
    $hash_pbkdf2 = hash_pbkdf2("sha256", $hash_salteado, $salt, $iterations);
?>

<?php
/*
 * Contexto do backend
 */


    if(isset($hash_pbkdf2) && isset($nonce)){
        $salt = "";
        $passwd = "";
        /*
         * Mesma interação que o frontend conhece
         */
        $iterations = 1000;
        $nonce_array = str_split($nonce);
        //Mesmo passo do front, descobrindo qual é o salt baseado em uma lógioca pré-acordada entre as partes
        $array_regra_salt = array('10','1','30','12','40','5','50','22','1',(count($nonce_array)-1), (int)(count($nonce_array)-1 * 2 / 9 -49));
        foreach ($array_regra_salt as $value) {
            $salt .= $nonce_array[$value];
        }

        /*
         * Vamos considerar que o hash_salvo_ldap ja foi recuperado com o hash do usuário (que passou, duas vezes pelo processo de sha256) e agora vamos 
         * adicionar o salt ao final e aplicar mais um sha256
         */
        $hashValid = hash('sha256',$hash_salvo_ldap.$salt);

        /*
         * Mesmo esquema, passei por um pbkdf2 utilizando o mesmo salt (descoberto pela logica pré-acordada pelas partes)
         */
        $hashValid_pbkdf2 = hash_pbkdf2("sha256", $hashValid, $salt, $iterations);

        /**
         * Agora, temos dois hashs iguais. (um gerado pelo front e o outro pelo backend) essa tecnica é muito utilizada quando vamos autenticar o usuário à cada requisição etc.
         */
        echo $hashValid_pbkdf2;
        echo "<br/>";
        echo $hash_pbkdf2;
    }
?>