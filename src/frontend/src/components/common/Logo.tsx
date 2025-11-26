interface LogoProps {
  className?: string;
  size?: 'sm' | 'md' | 'lg';
  showText?: boolean;
  variant?: 'light' | 'dark';
}

export function Logo({ className = '', size = 'md', showText = true, variant = 'light' }: LogoProps) {
  const sizes = {
    sm: { icon: 32, text: 'text-lg' },
    md: { icon: 40, text: 'text-2xl' },
    lg: { icon: 56, text: 'text-4xl' },
  };

  const { icon, text } = sizes[size];

  const colors = {
    light: { bg: '#1a1f36', accent: '#f7e547', textMain: 'text-primary' },
    dark: { bg: '#f7e547', accent: '#1a1f36', textMain: 'text-white' },
  };

  const { bg, accent } = colors[variant];

  return (
    <div className={`flex items-center gap-2 ${className}`}>
      <svg
        width={icon}
        height={icon}
        viewBox="0 0 64 64"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        className="flex-shrink-0"
      >
        {/* Hexagon base - bee/tech reference */}
        <path
          d="M32 4L56 18V46L32 60L8 46V18L32 4Z"
          fill={bg}
          stroke={accent}
          strokeWidth="2"
        />

        {/* Inner hexagon glow */}
        <path
          d="M32 12L48 22V42L32 52L16 42V22L32 12Z"
          fill={bg}
          stroke={accent}
          strokeWidth="1"
          opacity="0.5"
        />

        {/* Sound wave / whisper lines - left */}
        <path
          d="M20 28C17 30 17 34 20 36"
          stroke={accent}
          strokeWidth="2"
          strokeLinecap="round"
          fill="none"
        />
        <path
          d="M16 24C11 28 11 36 16 40"
          stroke={accent}
          strokeWidth="2"
          strokeLinecap="round"
          fill="none"
          opacity="0.7"
        />
        <path
          d="M12 20C5 26 5 38 12 44"
          stroke={accent}
          strokeWidth="2"
          strokeLinecap="round"
          fill="none"
          opacity="0.4"
        />

        {/* Sound wave / whisper lines - right */}
        <path
          d="M44 28C47 30 47 34 44 36"
          stroke={accent}
          strokeWidth="2"
          strokeLinecap="round"
          fill="none"
        />
        <path
          d="M48 24C53 28 53 36 48 40"
          stroke={accent}
          strokeWidth="2"
          strokeLinecap="round"
          fill="none"
          opacity="0.7"
        />
        <path
          d="M52 20C59 26 59 38 52 44"
          stroke={accent}
          strokeWidth="2"
          strokeLinecap="round"
          fill="none"
          opacity="0.4"
        />

        {/* Center dot - AI core */}
        <circle cx="32" cy="32" r="6" fill={accent} />
        <circle cx="32" cy="32" r="3" fill={bg} />
      </svg>

      {showText && (
        <span className={`font-bold ${text} tracking-tight`}>
          <span className="text-accent">W</span>
          <span className={variant === 'dark' ? 'text-white' : 'text-primary'}>hisper</span>
        </span>
      )}
    </div>
  );
}

export default Logo;
