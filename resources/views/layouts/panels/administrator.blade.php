<flux:sidebar.header>
    <flux:sidebar.brand
        href="#"
        logo="https://fluxui.dev/img/demo/logo.png"
        logo:dark="https://fluxui.dev/img/demo/dark-mode-logo.png"
        name="Acme Inc."
    />
    <flux:sidebar.collapse class="lg:hidden" />
</flux:sidebar.header>
<flux:sidebar.search placeholder="Search..." />
<flux:sidebar.nav>
    <flux:sidebar.item icon="home" href="#" current>Home</flux:sidebar.item>
    <flux:sidebar.item icon="inbox" badge="12" href="#">Inbox</flux:sidebar.item>
    <flux:sidebar.item icon="document-text" href="#">Documents</flux:sidebar.item>
    <flux:sidebar.item icon="calendar" href="#">Calendar</flux:sidebar.item>
    <flux:sidebar.group expandable heading="Favorites" class="grid">
        <flux:sidebar.item href="#">Marketing site</flux:sidebar.item>
        <flux:sidebar.item href="#">Android app</flux:sidebar.item>
        <flux:sidebar.item href="#">Brand guidelines</flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
