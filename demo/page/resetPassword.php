<div class="row">
    <div class="col-sm-6 col-sm-offset-3">
        <h2>Reset Password</h2>

        <hr/>

        <form method="post" action="ps/reset_password.php">
            <p>Enter the email associated with your account</p>

            <div class="form-group">
                <input name="Email" type="text" required autofocus class="form-control" placeholder="Email Address">
            </div>

            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary">Reset Password</button>
                <br/>
                <a href="login">Login</a>
            </div>
        </form>

    </div>
</div>