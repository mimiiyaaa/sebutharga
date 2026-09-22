<?php

test('guests are redirected to login from the application home page', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
