
<div class="container my-4">
	<div class="card">
		<div class="card-header"><strong>Timetable</strong></div>
		<div class="card-body p-0">
			<table class="table table-bordered text-center align-middle mb-0">
				<thead class="table-light">
					<tr>
						<th></th>
						<th>MONDAY</th>
						<th>TUESDAY</th>
						<th>WEDNESDAY</th>
						<th>THURSDAY</th>
						<th>FRIDAY</th>
						<th>SATURDAY</th>
						<th>SUNDAY</th>
					</tr>
				</thead>
						<td>Same list</td>
						@endforeach
						@foreach(['Fri','Sat','Sun'] as $d)
						<td>Same list</td>
						@endforeach
					</tr>
					<tr>
						<td><strong>4:45pm – 6:45pm<br><span class="text-muted">(Mon–Thu)</span><br>2:15pm – 4:15pm<br><span class="text-muted">(Fri–Sun)</span></strong></td>
						@foreach(['Mon','Tue','Wed','Thu'] as $d)
						<td>Same list</td>
						@endforeach
						@foreach(['Fri','Sat','Sun'] as $d)
						<td>Same list</td>
						@endforeach
					</tr>
					<tr>
						<td><strong>7:00pm – 9:00pm<br><span class="text-muted">(Mon–Thu)</span><br>4:30pm – 6:30pm<br><span class="text-muted">(Fri–Sun)</span></strong></td>
						@foreach(['Mon','Tue','Wed','Thu'] as $d)
						<td>Same list</td>
						@endforeach
						@foreach(['Fri','Sat','Sun'] as $d)
						<td>Same list</td>
						@endforeach
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
