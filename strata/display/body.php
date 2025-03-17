<main class="container-fluid mt-5 mb-5">
    <div class="px-3 text-center">
        <h1>Strata Management System</h1>
        <p class="lead">Select your role to access the system</p>
    </div>
      
    <div class="row mx-auto justify-content-center">
        <!-- Owner Card -->
        <div class="col-md-3 col-sm-6 mb-4 align-self-center">
            <div class="card h-100">
                <div class="card-body text-center d-flex flex-column">
                    <h5 class="card-title">Owner</h5>
                    <p class="card-text flex-grow-1">Access your owner account and view council meetings</p>
                    <!-- <button type="button" class="btn btn-primary mt-auto" data-bs-toggle="modal" data-bs-target="#ownerLoginModal">Go</button> -->
                    <a href="/strata/owner/owner-viewer/owner-login.php" class="btn btn-primary">Login</a>
                </div>
            </div>
        </div>

        <!-- Staff Card -->
        <div class="col-md-3 col-sm-6 mb-4 align-self-center">
            <div class="card h-100">
                <div class="card-body text-center d-flex flex-column">
                    <h5 class="card-title">Staff</h5>
                    <p class="card-text flex-grow-1">Manage staff accounts and related events</p>
                    <!-- <button type="button" class="btn btn-primary mt-auto" data-bs-toggle="modal" data-bs-target="#staffLoginModal">Login</button> -->
                    <a href="/strata/staff/staff-viewer/staff-viewer.php" class="btn btn-primary">Login</a>
                </div>
            </div>           
        </div>

        <!-- Strata Manager Card -->
        <div class="col-md-3 col-sm-6 mb-4 align-self-center">
            <div class="card h-100">
                <div class="card-body text-center d-flex flex-column">
                    <h5 class="card-title">Admin</h5>
                    <p class="card-text flex-grow-1">Manage owners, properties, staffs and other features</p>
                    <button type="button" class="btn btn-primary mt-auto" data-bs-toggle="modal" data-bs-target="#managerLoginModal">Login</button>
                </div>
            </div>           
        </div>
        
        
        
        <!-- Company Owner Card -->
        <!-- <div class="col-md-3 col-sm-6 mb-4 align-self-center">
            <div class="card h-100">
                <div class="card-body text-center d-flex flex-column">
                    <h5 class="card-title">Company Owner</h5>
                    <p class="card-text flex-grow-1">Manage your company and related information</p>
                    <button type="button" class="btn btn-primary mt-auto" data-bs-toggle="modal" data-bs-target="#companyLoginModal">Login</button>
                </div>
            </div>           
        </div> -->
    </div>
</main>

<!-- Owner Login Modal -->
<div class="modal fade" id="ownerLoginModal" tabindex="-1" aria-labelledby="ownerLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ownerLoginModalLabel">Owner Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/strata/login.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="role" value="owner">
                    <div class="mb-3">
                        <label for="ownerUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="ownerUsername" name="username" value="owner" required>
                    </div>
                    <div class="mb-3">
                        <label for="ownerPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="ownerPassword" name="password" value="000" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Strata Manager Login Modal -->
<div class="modal fade" id="managerLoginModal" tabindex="-1" aria-labelledby="managerLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="managerLoginModalLabel">Strata Manager Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/strata/login.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="role" value="manager">
                    <div class="mb-3">
                        <label for="managerUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="managerUsername" name="username" value="manager" required>
                    </div>
                    <div class="mb-3">
                        <label for="managerPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="managerPassword" name="password" value="000" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Staff Login Modal -->
<div class="modal fade" id="staffLoginModal" tabindex="-1" aria-labelledby="staffLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staffLoginModalLabel">Staff Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/strata/login.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="role" value="staff">
                    <div class="mb-3">
                        <label for="staffUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="staffUsername" name="username" value="staff" required>
                    </div>
                    <div class="mb-3">
                        <label for="staffPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="staffPassword" name="password" value="000" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Company Owner Login Modal -->
<div class="modal fade" id="companyLoginModal" tabindex="-1" aria-labelledby="companyLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="companyLoginModalLabel">Company Owner Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/strata/login.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="role" value="companyowner">
                    <div class="mb-3">
                        <label for="companyUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="companyUsername" name="username" value="companyowner" required>
                    </div>
                    <div class="mb-3">
                        <label for="companyPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="companyPassword" name="password" value="000" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
  // Add this script to the bottom of your HTML file before the closing </body> tag
document.addEventListener('DOMContentLoaded', function() {
  // Create a function to automatically submit the owner login
  function autoLoginOwner() {
    // Get the submit button from the owner form
    const ownerForm = document.querySelector('#ownerLoginModal form');
    if (ownerForm) {
      ownerForm.submit();
    }
  }
  
  // Modify the Owner button to bypass the modal and submit directly
  const ownerButton = document.querySelector('[data-bs-target="#ownerLoginModal"]');
  if (ownerButton) {
    // Remove the modal attributes
    ownerButton.removeAttribute('data-bs-toggle');
    ownerButton.removeAttribute('data-bs-target');
    
    // Add click handler to submit the form directly
    ownerButton.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Create and submit a form programmatically
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '/strata/login.php';
      form.style.display = 'none';
      
      // Add the necessary input fields
      const roleInput = document.createElement('input');
      roleInput.type = 'hidden';
      roleInput.name = 'role';
      roleInput.value = 'owner';
      
      const usernameInput = document.createElement('input');
      usernameInput.type = 'hidden';
      usernameInput.name = 'username';
      usernameInput.value = 'owner';
      
      const passwordInput = document.createElement('input');
      passwordInput.type = 'hidden';
      passwordInput.name = 'password';
      passwordInput.value = '000';
      
      // Append inputs to form
      form.appendChild(roleInput);
      form.appendChild(usernameInput);
      form.appendChild(passwordInput);
      
      // Append form to body and submit
      document.body.appendChild(form);
      form.submit();
    });
  }
});
</script>