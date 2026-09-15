<?php

it('protects the students, revenues and payments endpoints', function () {
    $this->getJson('/api/v1/students')->assertUnauthorized();
    $this->getJson('/api/v1/revenues')->assertUnauthorized();
    $this->postJson('/api/v1/payments')->assertUnauthorized();
});

it('rejects invalid students pagination', function () {
    $this->withoutMiddleware()
        ->getJson('/api/v1/students?per_page=101')
        ->assertStatus(422);
});

it('rejects invalid revenue filters', function () {
    $this->withoutMiddleware()
        ->getJson('/api/v1/revenues?period=week')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['period']);
});

it('rejects incomplete payment data', function () {
    $this->withoutMiddleware()
        ->postJson('/api/v1/payments', ['student_id' => 'not-a-student'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['student_id', 'amount', 'currency']);
});
