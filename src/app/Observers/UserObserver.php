<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Обработка ПЕРЕД созданием записи
     *
     * @param User $user
     * @return void|false
     */
    public function creating(User $user)
    {
        // $test[] = $user->isDirty(); // было ли что-то изменено
        // $test[] = $user->isDirty('first_name'); // было ли изменено конкретное поле
        // $test[] = $user->getDirty(); // получить поля, которые были изменены
        // $test[] = $user->getAttribute('first_name'); // получить новое значение
        // $test[] = $user->first_name; // аналогично верхнему
        // $test[] = $user->getOriginal('first_name'); // получить старое значение
        // dd($test);
        // логика по автозаполнения каких-то полей, типо slug и т.д. 
        // $this->setSlug($user);
        // отправка писем например
    }
    /**
     * Handle the user "created" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function created(User $user)
    {
        //
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        //
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
