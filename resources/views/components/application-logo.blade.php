<svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" fill="none" {{ $attributes }}>
    <defs>
        <linearGradient id="logoGrad" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
            <stop stop-color="#FF7043"/>
            <stop offset="1" stop-color="#BF360C"/>
        </linearGradient>
        <linearGradient id="logoShine" x1="0" y1="0" x2="0" y2="40" gradientUnits="userSpaceOnUse">
            <stop stop-color="rgba(255,255,255,0.18)"/>
            <stop offset="1" stop-color="rgba(255,255,255,0)"/>
        </linearGradient>
    </defs>
    <!-- Badge background -->
    <rect width="40" height="40" rx="12" fill="url(#logoGrad)"/>
    <!-- Gloss overlay -->
    <rect width="40" height="20" rx="12" fill="url(#logoShine)"/>
    <!-- Map pin body -->
    <path d="M20 4C13.925 4 9 8.925 9 15C9 21 15 30 19.4 35.2C19.72 35.6 20.28 35.6 20.6 35.2C25 30 31 21 31 15C31 8.925 26.075 4 20 4Z" fill="white"/>
    <!-- Orange inner circle -->
    <circle cx="20" cy="15" r="5.8" fill="#E84E1B"/>
    <!-- Lightning bolt (speed / fast delivery) -->
    <path d="M21.8 10.5L18.5 15.5H21.2L18.2 20L25 13.2H22L21.8 10.5Z" fill="white"/>
    <!-- Bottom shadow hint on badge -->
    <ellipse cx="20" cy="38.5" rx="8" ry="1.5" fill="rgba(0,0,0,0.12)"/>
</svg>
