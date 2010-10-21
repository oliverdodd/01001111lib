/*	Validate Class and functions - validate variables with regular
 *		expressions and callbacks.  Contains functionality to
 *		automatically validate all items on a page and execute a
 *		callback on success of failure
 *
 *	Copyright (c) 2007 Oliver C Dodd
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a 
 *  copy of this software and associated documentation files (the "Software"),
 *  to deal in the Software without restriction, including without limitation
 *  the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *  and/or sell copies of the Software, and to permit persons to whom the 
 *  Software is furnished to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL 
 *  THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 *  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 *  DEALINGS IN THE SOFTWARE.
 */
Validate = {
	/*--------------------------------------------------------------------*\
	|* COMMON REGULAR EXPRESSIONS                                         *|
	\*--------------------------------------------------------------------*/
	NOT_EMPTY:	/.+/,
	NUMBER:		/^[-+]?\b[0-9]*\.?[0-9]+\b$/,
	NONZERONUMBER:	/^[1-9]+[0-9]*\.?[0-9]*$/,
	ALPHA:		/^[A-Za-z]+$/,
	ALPHA_WS: 	/^[A-Za-z\s]+$/,
	ALPHANUMERIC:	/^[A-Za-z0-9]+$/,
	ALPHANUMERIC_WS:/^[A-Za-z0-9\s]+$/,
	
	SAFE:		/^[^\"\'<>\\]+$/,
	USERNAME:	/^[\w@.]+$/,
	
	YEAR:		/^[0-9]{4}$/,
	/* timestamp */
	DATE:		/^(\d{4})-(0[1-9]|1[012])-(0[1-9]|[12]\d|3[01])$/,
	TIME:		/^([0-1]\d|2[0-3]):([0-5]\d):([0-5]\d)$/,
	
	EMAIL:		/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i,
	PHONE_NUMBER:	/^[1-9]\d{2}-\d{3}\-\d{4}$/,
	ZIP_CODE:	/^\d{5}$/,
	
	isRegExp: function(r)
	{
		RegExp.prototype.isRegExp = true;
		var is_regexp = r.isRegExp;
		delete RegExp.prototype.isRegExp;
		return is_regexp;
	},
	
	/*--------------------------------------------------------------------*\
	|* VALIDATE                                                           *|
	\*--------------------------------------------------------------------*/
	v: function(variable,validators,element)
	{
		variable = String(variable);
		if (isArray(validators)) validators = Array.toObject(validators);
		if (!isObject(validators)||Validate.isRegExp(validators))
			validators = {0:validators};
		for (var i in validators) {
			var v = validators[i];
			if (isFunction(v)) { 
				if (!v(variable,element)) return 0;
			}
			else {	if (!variable.match(v)) return 0; }
		}
		return 1;
	},
	/*--------------------------------------------------------------------*\
	|* RUN                                                                *|
	\*--------------------------------------------------------------------*/
	/** run - search provided elements or all elements on a page looking for
	 *	the "requires" attribute, validate, and execute a callback.
	 *  @param callback	= function(element,success)
	 */
	run: function(callback,elements)
	{
		if (!callback) callback = function(element,success){};
		var valid = true,r = true,validators;
		if (elements === undefined) elements = $$("[requires]");
		if (isArray(elements)) elements = Array.toObject(elements);
		for (var i in elements) {// try {
			validators = eval(elements[i].getAttribute("requires"));
			if (!validators||elements[i].disabled) continue;
			valid = Validate.v(elements[i].value,validators,elements[i]);
			r &= valid;
			callback(elements[i],valid);
		} //catch(e) { continue; }
		return r;
	}
}