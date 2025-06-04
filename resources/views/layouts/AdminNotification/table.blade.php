@vite('resources/js/app.js')
<div id="admin-notifications-table">
    @foreach($notifications as $notification)
        <admin-notification :props="{{ json_encode($notification) }}"></admin-notification>
    @endforeach
</div>

