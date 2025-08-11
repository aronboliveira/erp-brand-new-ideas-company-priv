import { Card, CardContent } from "@mui/material";
import AuthPage from "../auth/page";
import { Parent } from "@/definitions/components";
export default function LoginPage({ children }: Parent) {
  return (
    <AuthPage>
      <Card id='login-card' className='max-w-md mx-auto my-8'>
        <CardContent>{children}</CardContent>
      </Card>
    </AuthPage>
  );
}
