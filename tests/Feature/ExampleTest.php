<?php

test('the home page sends guests to login', function () {
    $this->get(route('home'))->assertRedirect(route('login'));
});
