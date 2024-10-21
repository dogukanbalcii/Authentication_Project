# Symfony Authentication System

This project is an authentication system built with Symfony 6.4. It includes registration, login, role management, and security features.

## Features

### Registration
- **Email Validation:** Ensures a valid email format is provided during registration.
- **Password Requirements:** Password must be at least 6 characters long and contain both letters and numbers. Special characters are also allowed.
- **Role Selection:** Users can choose one of the following roles:
  - User
  - Admin
  - Super Admin
  Role options are dynamic and depend on the current user’s role:
  - Regular users or unauthenticated users can select either 'User' or 'Admin'.
  - Admins can also select 'Super Admin'.
- **Agree to Terms:** A checkbox for agreeing to terms is mandatory before registration is allowed.

### Login
- **Username and Password Validation:** Users must provide correct credentials (email and password). An error message ("Incorrect email or password") is displayed on failure.
- **Automatic Redirect:** If a user is already logged in, they are redirected to the homepage instead of seeing the login page.

### Security and Role Management
- **Role-Based Access Control:** The system supports multiple user roles with different access levels.
- **Logout Redirect:** After logging out, users are redirected to the homepage.

### Form Security
- **CSRF Protection:** Both registration and login forms are protected against CSRF attacks using Symfony's built-in token generation.
- **JWT Token:** The application uses JSON Web Tokens (JWT) for authentication, providing a stateless and secure method for user identity verification. JWT tokens are generated upon successful login and must be included in subsequent requests for access to protected resources, enhancing overall security.


# Screenshots
<img src="https://github.com/dogukanbalcii/Authentication_Project/blob/master/SS/1.png"
alt="Android Yemek Tarifi 5"/>
<br>
<img src="https://github.com/dogukanbalcii/Authentication_Project/blob/master/SS/2.png"
alt="Android Yemek Tarifi 5"/>
<br>
<img src="https://github.com/dogukanbalcii/Authentication_Project/blob/master/SS/3.png"
alt="Android Yemek Tarifi 5"/>
<br>
<img src="https://github.com/dogukanbalcii/Authentication_Project/blob/master/SS/4.png"
alt="Android Yemek Tarifi 5"/>
