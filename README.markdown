Authenticate
============

### Version 1.0 - 20120204

#### By Objective HTML / Justin Kimbrell

Overview
--------

Authenticate provides better support for member login and forgot password forms. [Full Documentation](http://www.objectivehtml.com/authenticate).

Login Form
----------

	{exp:authenticate:login_form
		auth_type="email"
		username_field="email"
		class="sixcol"
		return="some/return/url"}
		
		{if total_global_errors > 0}
		<div class="errors">
			
			<h3>Authentication Errors</h3>
			
			<ul class="bullets">
			{global_errors}
				<li>{error}</li>
			{/global_errors}
			</ul>
			
		</div>
		{/if}
		
		{if total_field_errors > 0}
		<div class="errors">
			
			<h3>Field Errors</h3>
			
			<ul class="bullets">
			{field_errors}
				<li>{error}</li>
			{/field_errors}
			</ul>
			
		</div>
		{/if}
		
		<ul class="plain">
			<li>
				<label for="email">E-mail</label>
				<input type="text" name="email" value="{if post:email}{post:email}{/if}" id="email" />
			</li>
			<li>
				<label for="password">Password</label>
				<input type="password" name="password" value="{if post:password}{post:password}{/if}" id="password" />
			</li>
		</ul>
		
		<button type="submit" class="black-button">Login</button>
		
	{/exp:authenticate:login_form}

Forgot Password
---------------
	
	{exp:authenticate:forgot_password
		class="sevencol"
		username_field="email"
		auth_type="email"}
		
		{if total_global_errors > 0}
		<div class="errors">
			
			<h3>Authentication Errors</h3>
			
			<ul class="bullets">
			{global_errors}
				<li>{error}</li>
			{/global_errors}
			</ul>
			
		</div>
		{/if}
		
		{if total_field_errors > 0}
		<div class="errors">
			
			<h3>Field Errors</h3>
			
			<ul class="bullets">
			{field_errors}
				<li>{error}</li>
			{/field_errors}
			</ul>
			
		</div>
		{/if}
		
		<label for="password">E-mail</label>
		<input type="text" name="email" value="" id="email" placeholder="you@example.org" />
		
		<button type="submit" class="black-button margin-top">Send E-mail</button>
		
	{/exp:authenticate:forgot_password}

License
-------
Channel Data is licensed using the BSD 2-Clause License. In a nutshell, do whatever you want with it so long as you leave the copyright information and don't take credit for my work. The idea is for everyone to benefit from the library. For a full copy of the license, refer to license.txt in the download package.

[Back to Top](#channeldata "Go to the top of the page")
