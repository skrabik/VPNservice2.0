@vite('resources/js/app.js')
<div id="admin-notifications-table">
    @foreach($notifications as $notification)
        <admin-notifications-table :props="{{ json_encode($notification) }}"></admin-notifications-table>
    @endforeach
</div>

