import { JSX } from "react";
export default function LandingVideo(): JSX.Element {
  return (
    <video
      preload='metadata'
      controls
      controlsList='nodownload nofullscreen'
      autoPlay
      muted
      playsInline
      loop
      crossOrigin='anonymous'
      disablePictureInPicture
      style={{
        maxWidth: "50vw",
        borderRadius: "0.5rem",
        marginLeft: "1rem",
      }}
    >
      <source src='/cybersecurity_analist.webm'></source>
    </video>
  );
}
