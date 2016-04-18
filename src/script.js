var bandwidth_data,
		bandwidth_last_index = 0,
		bandwidth_first_index = 0,
		bandwidth_data_size,
		bandwidth_graph_max_rate = 4,
		bandwidth_graph_px_per_ms = 0.01,
		bandwidth_graph_x_numbers,
		bandwidth_graph_y_numbers,
 		message_event_names = [	'INFO', 'NOTICE', 'WARN', 'ERR' ],
 		messages_hide = 1, // each bit means whether to display messages of the
 		//corresponding severity
 		get_events_timea = 0,
 		tor_options_row_group,
 		tor_options_value, // the input elements for each value
 		tor_options_default, // the checkboxes for whether to use default value
 		custom_commands_executed = null,
 		custom_commands_executed_scroll = null,
 		custom_command_console_jquery,
 		php_tor_controller_url,
 		tor_options_categories,
 		tor_options_number,
 		tor_options_name,
 		update_status_interval,
 		status_fields,
 		stream_tbody,
 		stream_contents = [],
		stream_elements = [],
		stream_num = 0,
		circuit_tbody,
		circuit_contents = [],
		circuit_elements = [],
		circuit_num = 0,
		orconn_tbody,
		orconn_contents = [],
		orconn_elements = [],
		orconn_num = 0,
		or_tbody,
		or_contents = [],
		or_elements = [],
		or_num = 0,
		data_from_server_list = null,
		data_from_server_list_end = null,
		update_status_handle_running = 0,
		messsages_data,
		messages_data_size,
		messages_data_last_index = 0,
		messages_tbody,
		command_response_box_jquery,
		concurrent_requests_num = 0,
		last_bandwidth_time,
		bandwidth_started = 0, // timestamp of last bandwidth data in seconds
		user_events = [],
		user_events_num = 0;

function strcmp(a, b) {
	return a < b ? -1 : a > b ? 1 : 0;
}

var geoip_tree = new RBTree(function(a, b) {
	return strcmp(a.ip, b.ip);
});

function bandwidth_graph_y_numbers_update() {
	var a = bandwidth_graph_max_rate >> 2;
	if (a < 0x400)
		for (var b = 1; b < 5; b++)
			bandwidth_graph_y_numbers[b].innerHTML = String(a * b);
	else if (a < 0x100000)
		for (var b = 1; b < 5; b++)
			bandwidth_graph_y_numbers[b].innerHTML = String(a * b >> 10) + 'K';
	else if (a < 0x40000000)
		for (var b = 1; b < 5; b++)
			bandwidth_graph_y_numbers[b].innerHTML = String(a * b >> 20) + 'M';
	else
		for (var b = 1; b < 5; b++)
			bandwidth_graph_y_numbers[b].innerHTML = String(a * b >> 30) + 'G';
}

function custom_command_request(command, handler) {
	$.post(php_tor_controller_url, {
		'action' : 'custom_command',
		'custom_command_command' : command
	}, handler);
}

function custom_command_handle_key(event) {
	var key = event.which || event.keyCode, new_custom_command_input, tmp;
	if (key == 13)// enter
	{
		new_custom_command_input = $('<div class="console_input"></div>')[0];
		custom_command_command = custom_command_input_box.value;
		tmp = custom_commands_executed;
		custom_commands_executed = {
			command : custom_command_command,
			last : tmp,
			next : null
		};
		if (tmp)
			tmp.next = custom_commands_executed;
		custom_commands_executed_scroll = null;
		new_custom_command_input.textContent = custom_command_command;
		custom_command_console_jquery.append(new_custom_command_input);
		custom_command_console.scrollTop = custom_command_console.scrollHeight;

		if (custom_command_command.substr(0, 9).toUpperCase() == 'SETEVENTS') {
			if (!custom_command_command[9])
				user_events_num = 0;
			else if (custom_command_command[9] == ' ') {
				user_events = custom_command_command.substr(10).toUpperCase()
						.split(' ');
				user_events.sort();
				user_events_num = user_events.length;
			}
		}

		custom_command_request(
				custom_command_command,
				function(data) {
					var new_custom_command_output =
							$('<div class="console_output"></div>'),
							new_custom_command_output_line,
							last_line, // position of start of current line in
							//response
							current_line;// position of end of current line in
							//response

					last_line = 0;
					while ((current_line = data.indexOf("\r\n", last_line))
							!= -1) {
						new_custom_command_output_line
								= $('<div class="console_output_line"></div>')
								[0];
						new_custom_command_output_line.textContent = data
								.substr(last_line, current_line - last_line);
						last_line = current_line + 2;
						new_custom_command_output
								.append(new_custom_command_output_line);
					}
					custom_command_console_jquery
							.append(new_custom_command_output[0]);
					custom_command_console.scrollTop
							= custom_command_console.scrollHeight;
				});
		custom_command_input_box.value = '';
	} else if (key == 38)// up
	{
		if (custom_commands_executed_scroll)
			tmp = custom_commands_executed_scroll.last;
		else
			tmp = custom_commands_executed;
		if (tmp) {
			custom_commands_executed_scroll = tmp;
			custom_command_input_box.value
					= custom_commands_executed_scroll.command;
		}
	} else if (key == 40)// down
	{
		if (custom_commands_executed_scroll) {
			custom_commands_executed_scroll
					= custom_commands_executed_scroll.next;
			if (custom_commands_executed_scroll)
				custom_command_input_box.value
						= custom_commands_executed_scroll.command;
			else
				custom_command_input_box.value = '';
		}
	}
}

function update_bandwidth_graph() {
	// bandwidth graph is from (90,50) to(690,350)
	var current_index, current_item, now, upload_path_content = '',
			download_path_content = '', x, x1, current_max_rate = 4,
			path_started = 0;

	current_index = bandwidth_last_index;
	while (current_index != bandwidth_first_index) {
		current_item = bandwidth_data[current_index];
		now = Date.now();
		x = (now - current_item.time) * bandwidth_graph_px_per_ms;
		current_item = bandwidth_data[current_index];
		while (current_item.upload > current_max_rate)
			current_max_rate <<= 1;
		while (current_item.download > current_max_rate)
			current_max_rate <<= 1;
		x = (now - current_item.time) * bandwidth_graph_px_per_ms;
		x1 = String(690 - x);
		upload_path_content += (path_started ? 'L' : 'M')
				+ x1
				+ ' '
				+ String(350 - current_item.upload * 300
						/ bandwidth_graph_max_rate);
		download_path_content += (path_started ? 'L' : 'M')
				+ x1
				+ ' '
				+ String(350 - current_item.download * 300
						/ bandwidth_graph_max_rate);
		if (x > 600)
			break;
		current_index++;
		if (current_index == bandwidth_data_size)
			current_index = 0;
		path_started = 1;
	}
	if (current_max_rate != bandwidth_graph_max_rate) {
		bandwidth_graph_max_rate = current_max_rate;
		bandwidth_graph_y_numbers_update();
	} else {
		upload_path.setAttribute('d', upload_path_content);
		download_path.setAttribute('d', download_path_content);
	}

	requestAnimationFrame(update_bandwidth_graph);
}

function tor_options_change_category(category) {
	var a = tor_options_categories[category];
	for (var b = 0; b < tor_options_number; b++) {
		if ((a[b >> 5] >> (b & 31)) & 1)
			tor_options_table_row[b].style.display = 'table-row-group';
		else
			tor_options_table_row[b].style.display = 'none';
	}
}

function custom_command_popup(command) {
	custom_command_request(
			command,
			function(data) {
				var new_custom_command_output_line, last_line, current_line;
				command_command_box.textContent = command;
				last_line = 0;
				while ((current_line = data.indexOf("\r\n", last_line)) != -1) {
					new_custom_command_output_line
							= $('<div class="console_output_line"></div>')[0];
					new_custom_command_output_line.textContent = data.substr(
							last_line, current_line - last_line);
					last_line = current_line + 2;
					command_response_box_jquery
							.append(new_custom_command_output_line);
				}
				command_.style.display = 'block';
			});
}

function sort(array, array_sat, start, end) {
	var a = start, b = end, c = array[start], d = array[end],
			e = array_sat[start], f = array_sat[end];
	while (a < b) {
		if (d < c) {
			array[a] = d;
			array_sat[a] = f;
			a++;
			d = array[a];
			f = array_sat[a];
		} else {
			array[b] = d;
			array_sat[b] = f;
			b--;
			d = array[b];
			f = array_sat[b];
		}
	}
	array[a] = c;
	array_sat[a] = e;
	if (a > start + 1)
		sort(array, array_sat, start, a - 1);
	if (b + 1 < end)
		sort(array, array_sat, b + 1, end);
}

function update_status_handle(data) {
	/*
	 * data will be empty if something fails on the server side.
	 *
	 * Otherwise, he first 8 lines are the following status of tor:
	 * 	version
	 * 	network-liveness
	 * 	status/bootstrap-phase
	 * 	status/circuit-established
	 * 	status/enough-dir-info
	 * 	status/good-server-descriptor
	 * 	status/accepted-server-descriptor
	 * 	status/reachability-succeeded
	 * The next line is a number num in decimal meaning the lines for stream
	 * status.
	 * The next num lines are stream status, with " " at the end of each line.
	 * The next line is a number num in decimal meaning the number of lines for
	 * OR connection status.
	 * The next num lines are OR connection status, with " " at the end of each
	 * line.
	 * The next line is a number num in decimal meaning the lines for circuit
	 * status.
	 * The next num lines are circuit status. Each entry is
	 * 	id
	 * 	status
	 * 	build flag
	 * 	time created
	 * 	path
	 * Seperators are " ".
	 * The next line is a number num in decimal meaning the entries for OR
	 * status.
	 * The next num lines are OR status. Each entry is
	 * 	nickname
	 * 	identity
	 * 	digest
	 * 	publication
	 * 	ip
	 * 	ORPort
	 * 	DIRPort
	 * 	IPv6 addresses, each ending with ';'
	 * 	flags
	 * 	version
	 * 	bandwidth
	 * 	portlist
	 * Seperators are "\t".
	 * Each of the next lines is a timestamp in miliseconds in decimal followed
	 * by a line of response for one of the following asynchronous events
	 * without "650" at the beginning of the line.
	 * 	bw
	 * 	info
	 * 	warn
	 * 	debug
	 * 	err
	 * Line breaks are "\n".
	 *
	 * When the first time OR list is received, more than 1 second may be taken
	 * to put the OR list into DOM. So we have update_status_handle_running to
	 * make sure only 1 instance of this function runs at a time.
	 */

	if (data_from_server_list) {
		var new_node = {
			data : data,
			next : null
		};
		data_from_server_list_end.next = new_node;
		data_from_server_list_end = new_node;
	} else {
		data_from_server_list = {
			data : data,
			next : null
		};
		data_from_server_list_end = data_from_server_list;
	}
	if (!update_status_handle_running) {
		update_status_handle_running = 1;
		var geoip_todo_addr = Array(1), geoip_todo_element = Array(1),
				geoip_todo_num = 0, geoip_todo_max = 1;
		while (1) {
			if (data_from_server_list) {
				if (data_from_server_list.data) {
					var last_line = 0, // position of start of current line
							current_line, // position of end of current line
							num, new_list, line, new_list_elements, new_element,
							new_element_1, new_element_1_jquery,
							new_element_jquery;

					status_fields[0].textContent = 'succeeded';
					for (var a = 1; a < 9; a++) {
						current_line = data_from_server_list.data.indexOf('\n',
								last_line);
						status_fields[a].textContent
								= data_from_server_list.data
								.substr(last_line, current_line - last_line);
						last_line = current_line + 1;
					}

					// to update stream status
					current_line = data_from_server_list.data.indexOf('\n',
							last_line);
					num = Number(line = data_from_server_list.data.substr(
							last_line, current_line - last_line));
					if (isNaN(num)) {
						update_status_handle_running = 0;
						console.log(
						"invalid value for number of lines for stream status\n"
										+ line);
						return;
					}
					last_line = current_line + 1;
					new_list = Array(num);
					new_list_elements = Array(num);
					for (var a = 0; a < num; a++) {
						var b, c, d;
						current_line = data_from_server_list.data.indexOf('\n',
								last_line);
						line = data_from_server_list.data.substr(last_line,
								current_line - last_line);
						new_list[a] = line;
						b = 0;
						c = stream_num;
						while (1) {
							var e, f, g;
							if (b < c) {
								d = (b + c) >> 1;
								if (stream_contents[d] > line)
									c = d;
								else if (stream_contents[d] == line) {
									new_list_elements[a] = stream_elements[d];
									stream_elements[d] = null;
									break;
								} else
									b = d + 1;
							} else {
								new_element = $('<tr></tr>');
								e = last_line;
								for (g = 0; g < 4; g++) {
									f = data_from_server_list.data.indexOf(' ',
											e);
									new_element_1 = $('<td></td>')[0];
									new_element_1.textContent
											= data_from_server_list.data
													.substr(e, f - e);
									new_element.append(new_element_1);
									e = f + 1;
								}
								new_element = new_element[0];
								stream_tbody.append(new_element);
								new_list_elements[a] = new_element;
								break;
							}
						}
						last_line = current_line + 1;
					}
					for (var a = 0; a < stream_num; a++)
						if (stream_elements[a])
							$(stream_elements[a]).remove();
					stream_number.textContent = num;
					stream_num = num;
					sort(new_list, new_list_elements, 0, num - 1);
					stream_contents = new_list;
					stream_elements = new_list_elements;

					// to update orconn status
					current_line = data_from_server_list.data.indexOf('\n',
							last_line);
					num = Number(line = data_from_server_list.data.substr(
							last_line, current_line - last_line));
					if (isNaN(num)) {
						update_status_handle_running = 0;
						console
								.log(
						"invalid value for number of lines for orconn status\n"
										+ line);
						return;
					}
					last_line = current_line + 1;
					new_list = Array(num);
					new_list_elements = Array(num);
					for (var a = 0; a < num; a++) {
						var b, c, d;
						current_line = data_from_server_list.data.indexOf('\n',
								last_line);
						line = data_from_server_list.data.substr(last_line,
								current_line - last_line);
						new_list[a] = line;
						b = 0;
						c = orconn_num;
						while (1) {
							var e, f, g;
							if (b < c) {
								d = (b + c) >> 1;
								if (orconn_contents[d] > line)
									c = d;
								else if (orconn_contents[d] == line) {
									new_list_elements[a] = orconn_elements[d];
									orconn_elements[d] = null;
									break;
								} else
									b = d + 1;
							} else {
								new_element = $('<tr></tr>');
								e = last_line;
								for (g = 0; g < 2; g++) {
									f = data_from_server_list.data.indexOf(' ',
											e);
									new_element_1 = $('<td></td>')[0];
									new_element_1.textContent
											= data_from_server_list.data
											.substr(e, f - e);
									new_element.append(new_element_1);
									e = f + 1;
								}
								new_element = new_element[0];
								orconn_tbody.append(new_element);
								new_list_elements[a] = new_element;
								break;
							}
						}
						last_line = current_line + 1;
					}
					for (var a = 0; a < orconn_num; a++)
						if (orconn_elements[a])
							$(orconn_elements[a]).remove();
					orconn_number.textContent = num;
					orconn_num = num;
					sort(new_list, new_list_elements, 0, num - 1);
					orconn_contents = new_list;
					orconn_elements = new_list_elements;

					// to update circuit status
					current_line = data_from_server_list.data.indexOf('\n',
							last_line);
					num = Number(line = data_from_server_list.data.substr(
							last_line, current_line - last_line));
					if (isNaN(num)) {
						update_status_handle_running = 0;
						console
								.log(
						"invalid value for number of lines for circuit status\n"
										+ line);
						return;
					}
					last_line = current_line + 1;
					new_list = Array(num);
					new_list_elements = Array(num);
					for (var a = 0; a < num; a++) {
						var b, c, d;
						current_line = data_from_server_list.data.indexOf('\n',
								last_line);
						line = data_from_server_list.data.substr(last_line,
								current_line - last_line);
						new_list[a] = line;
						b = 0;
						c = circuit_num;
						while (1) {
							var e, f, g;
							if (b < c) {
								d = (b + c) >> 1;
								if (circuit_contents[d] > line)
									c = d;
								else if (circuit_contents[d] == line) {
									new_list_elements[a] = circuit_elements[d];
									circuit_elements[d] = null;
									break;
								} else
									b = d + 1;
							} else {
								new_element = $('<tr></tr>');
								e = last_line;
								for (g = 0; g < 12; g++) {
									f = data_from_server_list.data.indexOf(' ',
											e);
									new_element_1 = $('<td></td>')[0];
									new_element_1.textContent
											= data_from_server_list.data
													.substr(e, f - e);
									new_element.append(new_element_1);
									e = f + 1;
								}
								new_element = new_element[0];
								circuit_tbody.append(new_element);
								new_list_elements[a] = new_element;
								break;
							}
						}
						last_line = current_line + 1;
					}
					for (var a = 0; a < circuit_num; a++)
						if (circuit_elements[a])
							$(circuit_elements[a]).remove();
					circuit_number.textContent = num;
					circuit_num = num;
					sort(new_list, new_list_elements, 0, num - 1);
					circuit_contents = new_list;
					circuit_elements = new_list_elements;

					// to update OR status
					current_line = data_from_server_list.data.indexOf('\n',
							last_line);

					num = Number(line = data_from_server_list.data.substr(
							last_line, current_line - last_line));
					if (isNaN(num)) {
						update_status_handle_running = 0;
						console
								.log(
							"invalid value for number of lines for or status\n"
										+ line);
						return;
					}
					last_line = current_line + 1;
					new_list = Array(num);
					new_list_elements = Array(num);
					for (var a = 0; a < num; a++) {
						var b, c, d;
						current_line = data_from_server_list.data.indexOf('\n',
								last_line);
						line = data_from_server_list.data.substr(last_line,
								current_line - last_line);
						new_list[a] = line;
						b = 0;
						c = or_num;
						while (1) {
							var e, f, g;
							if (b < c) {
								d = (b + c) >> 1;
								if (or_contents[d] > line)
									c = d;
								else if (or_contents[d] == line) {
									new_list_elements[a] = or_elements[d];
									or_elements[d] = null;
									break;
								} else
									b = d + 1;
							} else {
								var new_element_2, ipv6_addr_list, h, i;

								new_element = $('<tr></tr>');
								e = last_line;

								// nickname, identity, digest, and publication
								for (g = 0; g < 4; g++) {
									f = data_from_server_list.data.indexOf(
											'\t', e);
									new_element_1 = $('<td></td>')[0];
									new_element_1.textContent
											= data_from_server_list.data
													.substr(e, f - e);
									new_element.append(new_element_1);
									e = f + 1;
								}

								// IP
								f = data_from_server_list.data.indexOf('\t',
										f + 1);
								new_element_1_jquery = $('<td></td>');
								new_element_1 = new_element_1_jquery[0];
								ip_addr = data_from_server_list.data.substr(e,
										f - e);
								new_element_1.textContent = ip_addr;
								new_element_2 = $('<span></span>')[0];
								new_element_1_jquery.append(new_element_2);

								// to resize the geoip_todo arrays if they are
								// not big enough
								if (geoip_todo_num == geoip_todo_max) {
									var tmp_size = geoip_todo_max << 1,
											tmp_array;
									tmp_array = Array(tmp_size);
									for (geoip_todo_num = 0;
											geoip_todo_num < geoip_todo_max;
											geoip_todo_num++)
										tmp_array[geoip_todo_num]
												= geoip_todo_addr
														[geoip_todo_num];
									geoip_todo_addr = tmp_array;
									tmp_array = Array(tmp_size);
									for (geoip_todo_num = 0;
										geoip_todo_num < geoip_todo_max;
										geoip_todo_num++)
										tmp_array[geoip_todo_num]
												= geoip_todo_element
														[geoip_todo_num];
									geoip_todo_element = tmp_array;
									tmp_array = Array(tmp_size);
									geoip_todo_max = tmp_size;
								}
								geoip_todo_addr[geoip_todo_num] = ip_addr;
								geoip_todo_element[geoip_todo_num]
										= new_element_2;
								geoip_todo_num++;
								new_element.append(new_element_1);
								e = f + 1;

								// OR port and DIR port
								for (g = 0; g < 2; g++) {
									f = data_from_server_list.data.indexOf(
											'\t', e);
									new_element_1 = $('<td></td>')[0];
									new_element_1.textContent
											= data_from_server_list.data
											.substr(e, f - e);
									new_element.append(new_element_1);
									e = f + 1;
								}

								// IPv6 address
								f = data_from_server_list.data.indexOf('\t', e);
								ipv6_addr_list = data_from_server_list.data
										.substr(e, f - e);
								new_element_1_jquery = $('<td></td>');
								h = 0;
								while ((i = ipv6_addr_list.indexOf(';', 'h'))
										!= -1) {
									var new_element_3, new_element_3_jquery;
									if (ipv6_addr_list[h] == '[')
										ip_addr = ipv6_addr_list.substr(h + 1,
												ipv6_addr_list.indexOf(']',
														h + 1)
														- h - 1);
									else
										ip_addr = ipv6_addr_list.substr(h, i
												- h);
									new_element_3_jquery = $('<div></div>');
									new_element_3 = new_element_3_jquery[0];
									new_element_3.textContent = ip_addr;
									new_element_2 = $('<span></span>')[0];
									new_element_3_jquery.append(new_element_2);

									// to resize the geoip_todo arrays if they
									// are not big enough
									if (geoip_todo_num == geoip_todo_max) {
										var tmp_size = geoip_todo_max << 1,
												tmp_array;
										tmp_array = Array(tmp_size);
										for (geoip_todo_num = 0;
												geoip_todo_num < geoip_todo_max;
												geoip_todo_num++)
											tmp_array[geoip_todo_num]
													= geoip_todo_addr
															[geoip_todo_num];
										geoip_todo_addr = tmp_array;
										tmp_array = Array(tmp_size);
										for (geoip_todo_num = 0;
												geoip_todo_num< geoip_todo_max;
												geoip_todo_num++)
											tmp_array[geoip_todo_num]
													= geoip_todo_element
													[geoip_todo_num];
										geoip_todo_element = tmp_array;
										geoip_todo_max = tmp_size;
									}

									geoip_todo_addr[geoip_todo_num] = ip_addr;
									geoip_todo_element[geoip_todo_num]
											= new_element_2;
									geoip_todo_num++;
									new_element_1_jquery.append(new_element_3);
									h = i + 1;
								}
								new_element.append(new_element_1_jquery[0]);
								e = f + 1;

								// flags, bandwidth, portlist, and version
								for (g = 0; g < 4; g++) {
									f = data_from_server_list.data.indexOf(
											'\t', e);
									new_element_1 = $('<td></td>')[0];
									new_element_1.textContent
											= data_from_server_list.data
											.substr(e, f - e);
									new_element.append(new_element_1);
									e = f + 1;
								}
								new_element = new_element[0];
								or_tbody.append(new_element);
								new_list_elements[a] = new_element;
								break;
							}
						}
						last_line = current_line + 1;
					}
					for (var a = 0; a < or_num; a++)
						if (or_elements[a])
							$(or_elements[a]).remove();
					OR_number.textContent = num;
					or_num = num;
					sort(new_list, new_list_elements, 0, num - 1);
					or_contents = new_list;
					or_elements = new_list_elements;

					// to store asynchronous events
					while ((current_line = data_from_server_list.data.indexOf(
							'\n', last_line)) != -1) {
						var tmpstr, a, b, c, d, e, time, event_name;
						a = data_from_server_list.data.indexOf(' ', last_line);
						time = Number(tmpstr = data_from_server_list.data
								.substr(last_line, a - last_line));
						if (isNaN(time)) {
							console
									.log(
							"invalid value for time for asynchronous event\n"
											+ tmpstr);
						} else {
							a++;
							b = data_from_server_list.data.indexOf(' ', a)
							event_name = data_from_server_list.data.substr(a, b
									- a);
							b++;

							// If the event name matches any events entered from
							// the console, the event is added to the console.
							c = 0;
							d = user_events_num;
							while (c < d) {
								e = (c + d) >> 1;
								if (user_events[e] < event_name)
									c = e + 1;
								else if (user_events[e] > event_name)
									d = e;
								else {
									new_element_jquery = $(
									'<div class="console_output_line"></div>');
									new_element_1 = $(
									'<span class="custom_command_time"></span>')
									[0];
									new_element_1.textContent = new Date(time)
											.toGMTString();
									new_element_jquery.append(new_element_1);
									new_element_1 = $('<span></span>')[0];
									new_element_1.textContent = "650 "
											+ data_from_server_list.data
											.substr(a, current_line - a);
									new_element_jquery.append(new_element_1);
									new_element_1_jquery =
									$('<div class="console_output"></div>');
									new_element_1_jquery
											.append(new_element_jquery[0]);
									custom_command_console_jquery
											.append(new_element_1_jquery[0]);
									custom_command_console.scrollTop
									= custom_command_console.scrollHeight;
									break;
								}
							}

							if (event_name == "BW") {
								var upload, download, new_bandwidth_time;
								a = b;
								b = data_from_server_list.data.indexOf(' ', a);
								if (isNaN(download = Number(tmpstr
										= data_from_server_list.data
										.substr(a, b - a)))) {
									console
											.log(
											"invalid value for download rate\n"
													+ tmpstr);
								} else if (isNaN(upload = Number(tmpstr
										= data_from_server_list.data
										.substr(b + 1, current_line - b - 1))))
								{
									console
											.log(
											"invalid value for upload rate\n"
													+ tmpstr);
								} else {
									if (bandwidth_last_index)
										bandwidth_last_index--;
									else
										bandwidth_last_index =
											bandwidth_data_size - 1;
									if (bandwidth_first_index
											== bandwidth_last_index) {
										if (bandwidth_first_index)
											bandwidth_first_index--;
										else
											bandwidth_first_index =
												bandwidth_data_size - 1;
									}
									bandwidth_data[bandwidth_last_index] = {
										upload : upload,
										download : download,
										time : time
									};

									// a message is generated if the time
									// receiving bandwidth data is not
									// consecutive
									new_bandwidth_time = Math
											.floor(time / 1000);
									if (bandwidth_started) {
										if (new_bandwidth_time
												!= last_bandwidth_time + 1) {
											console
													.log(
					"time of receiving bandwidth data not consecutive\nold: "
															+
															last_bandwidth_time
															+ "\nnew: "
															+ new_bandwidth_time
															);
										}
									} else {
										bandwidth_started = 1;
									}
									last_bandwidth_time = new_bandwidth_time;

									bandwidth_graph_current_download_rate_number
											.innerHTML = String(download);
									bandwidth_graph_current_upload_rate_number
											.innerHTML = String(upload);
								}
							}

							else {
								// If event_name is not "BW", names for
								// notifications are tried.
								c = 0;
								while (1) {
									if (c == 4) {
										break;
									}
									if (message_event_names[c] == event_name) {
										messages_data_last_index++;
										if (messages_data_last_index
												== messages_data_size)
											messages_data_last_index = 0;
										if (messages_data
												[messages_data_last_index])
											$(messages_data
													[messages_data_last_index])
													.remove();
										new_element_jquery = $('<tr></tr>');
										new_element_jquery
												.addClass('message_cat_'
														+ event_name);
										new_element_1 = $('<td></td>')[0];
										new_element_1.textContent = new Date(
												time).toGMTString();
										new_element_jquery
												.append(new_element_1);
										new_element_1 = $('<td></td>')[0];
										new_element_1.textContent = event_name;
										new_element_jquery
												.append(new_element_1);
										new_element_1 = $('<td></td>')[0];
										new_element_1.textContent
												= data_from_server_list.data
												.substr(b, current_line - b);
										new_element_jquery
												.append(new_element_1);
										messages_tbody
												.prepend(new_element
														=
														new_element_jquery[0]);
										messages_data[messages_data_last_index]
												= new_element;
										if (messages_hide & (1 << c))
											new_element_jquery.hide();
										break;
									}
									c++;
								}
							}
						}
						last_line = current_line + 1;
					}
				} else
					status_fields[0].textContent = 'failed';
				data_from_server_list = data_from_server_list.next;
			} else {
				// We set data_from_server_list_end null so that the browser
				// knows to free the list.
				data_from_server_list_end = null;
				update_status_handle_running = 0;
				if (geoip_todo_num) {
					var geoip_todo_num_new = 0;
					for (var a = 0; a < geoip_todo_num; a++) {
						var country = geoip_tree.find({
							ip : geoip_todo_addr[a],
							country : null
						});
						if (country)
							geoip_todo_element[a].textContent = " ("
									+ country.country + ")";
						else {
							geoip_todo_addr[geoip_todo_num_new]
									= geoip_todo_addr[a];
							geoip_todo_element[geoip_todo_num_new]
									= geoip_todo_element[a];
							geoip_todo_num_new++;
						}
					}
					$.post(php_tor_controller_url, {
						'action' : 'geoip',
						'ip_addr' : geoip_todo_addr
								.slice(0, geoip_todo_num_new).join('-')
					}, function(data) {
						for (var a = 0; a < geoip_todo_num_new; a++) {
							var country = data.substr(a * 2, 2);
							geoip_tree.insert({
								ip : geoip_todo_addr[a],
								country : country
							});
							geoip_todo_element[a].textContent = " (" + country
									+ ")";
						}
					});
				}
				return;
			}
		}
	}
}

function update_status() {
	/*
	 * It is possible that too many requests occur at the same time in the
	 * browser, such as when openning many web pages at the same time. In that
	 * case, some requests will be held until the number of concurrent requests
	 * fall below max. We need the requests to occur at exactly the right time
	 * to get asynchronous events. So it will be useless if the request is held.
	 * So we limit that only 2 concurrent requests can exist. Note that this
	 * number of concurrent requests only includes the requests triggered by the
	 * function update_status.
	 */
	if (concurrent_requests_num < 2) {
		concurrent_requests_num++;
		$.ajax({
			type : 'POST',
			url : php_tor_controller_url,
			data : {
				'action' : 'update_status'
			},
			success : update_status_handle,
			complete : function() {
				concurrent_requests_num--;
			}
		});
	}
}

function initial_request_handle(data) {
	/*
	 * Data will be empty if something fails on the server side.
	 *
	 * Otherwise, the first line of response is a number in decimal n. The next
	 * n lines are 2 decimal numbers seperated by ",". The first is download
	 * rate. The second is upload rate. Each line represents 1 second. They are
	 * in chronological order. Line breaks are "\n".
	 */
	if (data) {
		var num, last_line = 0, current_line, tmpstr;
		current_line = data.indexOf('\n');
		if (isNaN(num = Number(tmpstr = data.substr(0, current_line)))) {
			console
					.log("invalid value for number of bandwidth data\n"
							+ tmpstr);
		} else {
			var upload, download, now;
			now = new Date().getTime();
			while (num) {
				var comma;
				last_line = current_line + 1;
				current_line = data.indexOf('\n', last_line);
				comma = data.indexOf(',', last_line);
				if (isNaN(download = Number(tmpstr = data.substr(last_line,
						comma - last_line)))) {
					console.log("invalid value for download rate\n" + tmpstr);
					break;
				}
				comma++;
				if (isNaN(upload = Number(tmpstr = data.substr(comma,
						current_line - comma)))) {
					console.log("invalid value for upload rate\n" + tmpstr);
					break;
				}

				if (bandwidth_last_index)
					bandwidth_last_index--;
				else
					bandwidth_last_index = bandwidth_data_size - 1;
				if (bandwidth_first_index == bandwidth_last_index) {
					if (bandwidth_first_index)
						bandwidth_first_index--;
					else
						bandwidth_first_index = bandwidth_data_size - 1;
				}
				bandwidth_data[bandwidth_last_index] = {
					upload : upload,
					download : download,
					time : now - num * 1000
				};

				num--;
			}
		}
	}
}

function body_loaded() {
	bandwidth_data = Array(bandwidth_data_size);
	messages_data = Array(messages_data_size);
	stream_tbody = $("#streams_list");
	circuit_tbody = $("#circuit_list");
	orconn_tbody = $("#orconn_list");
	or_tbody = $("#ORlist");
	messages_tbody = $("#messages_table_tbody");
	custom_command_console_jquery = $("#custom_command_console");
	bandwidth_graph_x_numbers = $('.bandwidth_graph_label_x_number');
	bandwidth_graph_y_numbers = $('.bandwidth_graph_label_y_number');
	update_bandwidth_graph();
	tor_options_table_row = $('.tor_options_table_row');
	tor_options_change_category(0);
	tor_options_value = $('.tor_options_value');
	tor_options_default = $('.tor_options_default_checkbox');
	status_fields = $('#status_table td');
	command_response_box_jquery = $("#command_response_box");
	$.post(php_tor_controller_url, {
		'action' : 'get_bandwidth_history'
	}, initial_request_handle).always(function() {
		update_status();
		setInterval(update_status, update_status_interval)
	});
}

function update_settings_button_handle() {
	var a = 'resetconf';
	for (var c = 0; c < tor_options_number; c++) {
		a += ' ' + tor_options_name[c];
		if (!tor_options_default[c].checked)
			a += '=\"' + tor_options_value[c].value + '\"';
	}
	custom_command_popup(a);
}

function update_messages_display(severity, checked) {
	var messages = $(".message_cat_" + message_event_names[severity]);
	if (checked) {
		messages_hide &= ~(1 << severity);
		messages.show();
	} else {
		messages_hide |= 1 << severity;
		messages.hide();
	}
}
