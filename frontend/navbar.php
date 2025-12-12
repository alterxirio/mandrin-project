<nav class="fixed w-full z-20 top-0 start-0 border-default" style="background-color: #B71C1C;">
  <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">

    <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse">
        <img src="https://flowbite.com/docs/images/logo.svg" class="h-7" alt="Flowbite Logo" />
        <span class="self-center text-xl font-semibold text-white whitespace-nowrap">Flowbite</span>
    </a>

    <button data-collapse-toggle="navbar-dropdown" type="button" 
            class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-white rounded-base md:hidden hover:bg-[#8E1616] focus:outline-none" 
            aria-controls="navbar-dropdown" aria-expanded="false">
        <span class="sr-only">Open main menu</span>
        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h14"/>
        </svg>
    </button>

    <div class="hidden w-full md:block md:w-auto" id="navbar-dropdown">
      <ul class="flex flex-col font-medium p-4 md:p-0 mt-4 rounded-base md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0">

        <li>
          <a href="#" class="block py-2 px-3 text-white nav-hover rounded">Home</a>
        </li>

        <li>
            <button id="dropdownNvbarButton" data-dropdown-toggle="dropdownNavbar" 
                    class="flex items-center justify-between w-full py-2 px-3 rounded font-medium text-white nav-hover md:w-auto">
              Dropdown 
              <svg class="w-4 h-4 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
              </svg>
          </button>

          <!-- Dropdown menu -->
          <div id="dropdownNavbar" class="z-10 hidden rounded-lg shadow-lg w-44 mt-2" style="background-color: #B71C1C;">
              <ul class="p-2 text-sm font-medium">
                <li><a href="#" class="inline-flex items-center w-full p-2 rounded text-white dropdown-hover">Dashboard</a></li>
                <li><a href="#" class="inline-flex items-center w-full p-2 rounded text-white dropdown-hover">Settings</a></li>
                <li><a href="#" class="inline-flex items-center w-full p-2 rounded text-white dropdown-hover">Earnings</a></li>
                <li><a href="#" class="inline-flex items-center w-full p-2 rounded text-white dropdown-hover">Sign out</a></li>
              </ul>
          </div>
        </li>

        <li><a href="#" class="block py-2 px-3 text-white nav-hover rounded">Services</a></li>
        <li><a href="#" class="block py-2 px-3 text-white nav-hover rounded">Pricing</a></li>
        <li><a href="#" class="block py-2 px-3 text-white nav-hover rounded">Contact</a></li>

      </ul>
    </div>
  </div>
</nav>
