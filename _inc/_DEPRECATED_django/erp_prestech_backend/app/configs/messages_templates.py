from typing import Any
def get_exception_class_message(instance: Any, add: str) -> str:
  meta_message = lambda a: f' \n Meta: {a}' if a else ''
  try:
    if (instance.__class__.__name__ == 'PermissionDenied'):
      return f'Django exception: Permission Denied.{meta_message(add)}'
    else:
      return f'#DEFAULT_CLASS_ERROR_MESSAGE. Class name ({instance.__class__.__name__}) was not matched.{meta_message(add)}'
  except Exception as e:
    return f"#DEFAULT_CLASS_ERROR_MESSAGE. Exception thrown when checking for class name.{meta_message(add)}"
  
def get_lacking_field_message(field: Any, context: str, add: str) -> str:
  context_msg = lambda a: f'. \n Calling context: {a if a else '#MISSING CONTEXT#'}' 
  meta_msg = lambda a: f'. \n Additional information: {a}' if a else ''
  try:
    return f'Required field {field.replace('_', '').capitalize()} was missing or malformed{context_msg(context)}{meta_msg(add)}'
  except Exception as e:
    return f'Error forming field message for {field or '#UNDEFINED FIELD#'}'