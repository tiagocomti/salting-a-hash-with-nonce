# salting-a-hash-with-nonce
A forma, simples, de você pegar uma senha, hashear com salt e um nonce. Utilizando de um desafio, conhecido pelo front e o backend, para chegar na mesma formula do salt e verificar se ambos os hashs estão iguais.

Quando podemos utilizar dessa forma de hash senha?

Simples, vamos supor que você tem uma aplicação e precisa fazer 1fa dela para com a sua aplicação backend (ou seja, toda request que o meu aplicativo mobile fizer ele precisa executar uma autenticação simples)
No primeiro login vou armazenar a senha do usuário (aplicando duas vezes o hash256 de forma segura), E a cada request vou adicionar no header esse hash com salt mais o nonce.
A aplicação vai recuperar da base esse mesmo hash, aplicar o salt e descobrir se aquele hash de senha é do usuário que chegou via webservice. 
