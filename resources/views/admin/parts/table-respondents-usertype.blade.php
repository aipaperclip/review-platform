@if(!$item->user->is_dentist)
	<span class="label label-warning">Patient</span>
@elseif($item->user->is_clinic)
	<span class="label label-info">Clinic</span>
@else
	<span class="label label-success">Dentist</span>
@endif