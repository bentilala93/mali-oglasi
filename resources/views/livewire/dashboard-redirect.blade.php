<?php

use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{layout, computed};

layout('layouts.app');

$userRole = computed(function () {
    return Auth::user()->roles->first()->name ?? 'customer';
});

?>

<div>
    @if($this->userRole === 'admin')
        @livewire('admin-dashboard')
    @else
        @livewire('customer-dashboard')
    @endif
</div> 