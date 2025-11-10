import json

output = {
  'html': '<h1>title jejaj</h1><p>Some text</p>'
}

# convert into JSON:
y = json.dumps(output)

# the result is a JSON string:
print(y)