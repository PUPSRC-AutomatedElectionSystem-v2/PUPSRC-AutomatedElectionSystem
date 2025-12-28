import argparse
import os
import platform
import sys
from abc import ABC, abstractmethod

class HostManager(ABC):
    @abstractmethod
    def get_hosts_path(self) -> str:
        pass

    @abstractmethod
    def read_hosts(self) -> list[str] | None:
        pass

    @abstractmethod
    def write_hosts(self, lines: list[str]) -> None:
        pass

class WindowsHostManager(HostManager):
    def get_hosts_path(self) -> str:
        return r"C:\Windows\System32\drivers\etc\hosts"

    def read_hosts(self) -> list[str] | None:
        try:
            with open(self.get_hosts_path(), 'r') as file:
                return file.readlines()
        except FileNotFoundError:
            print("Hosts file not found, starting with empty list.")
            return []
        except PermissionError:
            print("Permission denied reading hosts file. Run as administrator.")
            return None

    def write_hosts(self, lines: list[str]) -> None:
        try:
            with open(self.get_hosts_path(), 'w') as file:
                file.writelines(lines)
            print("Hosts file updated successfully.")
        except PermissionError:
            print("Permission denied. Run this script as an administrator to modify the hosts file.")

class UnixHostManager(HostManager):
    def get_hosts_path(self) -> str:
        return "/etc/hosts"

    def read_hosts(self) -> list[str] | None:
        try:
            with open(self.get_hosts_path(), 'r') as file:
                return file.readlines()
        except FileNotFoundError:
            print("Hosts file not found, starting with empty list.")
            return []
        except PermissionError:
            print("Permission denied reading hosts file. Run as root or with sudo.")
            return None

    def write_hosts(self, lines: list[str]) -> None:
        try:
            with open(self.get_hosts_path(), 'w') as file:
                file.writelines(lines)
            print("Hosts file updated successfully.")
        except PermissionError:
            print("Permission denied. Run as root or with sudo.")

class CustomHostManager(HostManager):
    def __init__(self, custom_path: str):
        self.custom_path = custom_path

    def get_hosts_path(self) -> str:
        return self.custom_path

    def read_hosts(self) -> list[str] | None:
        try:
            with open(self.custom_path, 'r') as file:
                return file.readlines()
        except FileNotFoundError:
            print("Hosts file not found, starting with empty list.")
            return []
        except PermissionError:
            print("Permission denied reading hosts file.")
            sys.exit(1)

    def write_hosts(self, lines: list[str]) -> None:
        try:
            with open(self.custom_path, 'w') as file:
                file.writelines(lines)
            print("Hosts file updated successfully.")
        except PermissionError:
            print("Permission denied writing to hosts file.")
            sys.exit(1)

def find_header(lines: list[str], header_comment: str) -> int:
    for i, line in enumerate(lines):
        if line.strip() == header_comment:
            return i
    return -1

def find_footer(lines: list[str], footer_comment: str) -> int:
    for i, line in enumerate(lines):
        if line.strip() == footer_comment:
            return i
    return -1

def add_header(lines: list[str], header_comment: str) -> None:
    if lines and not lines[-1].endswith('\n'):
        lines.append('\n' * 2)
    else:
        lines.append('\n')
    lines.append(header_comment + '\n')

def add_footer(lines: list[str], footer_comment: str) -> None:
    lines.append(footer_comment + '\n')

def check_entry_exists(lines: list[str], entry: str, header_index: int, footer_index: int) -> bool:
    for i in range(header_index + 1, footer_index):
        if lines[i].strip() == entry:
            return True
    return False

def insert_entry(lines: list[str], entry: str, footer_index: int) -> None:
    lines.insert(footer_index, entry + '\n')

def add_new_entry(lines: list[str], entry: str, header_comment: str, footer_comment: str) -> None:
    add_header(lines, header_comment)
    lines.append(entry + '\n')
    add_footer(lines, footer_comment)

def has_header_and_footer(header_index: int, footer_index: int) -> bool:
    return header_index != -1 and footer_index != -1 and header_index < footer_index

def entry_already_exists(lines: list[str], entry: str, header_index: int, footer_index: int) -> bool:
    return check_entry_exists(lines, entry, header_index, footer_index)

def manage_host_entry(lines: list[str], entry: str, header_comment: str, footer_comment: str, header_index: int, footer_index: int) -> bool:
    if has_header_and_footer(header_index, footer_index):
        print("Header and footer found, checking for existing entry...")
        if entry_already_exists(lines, entry, header_index, footer_index):
            print(f"Entry '{entry}' already exists.")
            return False
        print("Adding entry between header and footer.")
        insert_entry(lines, entry, footer_index)
    else:
        print("Header and footer not found, adding to end.")
        add_new_entry(lines, entry, header_comment, footer_comment)
    return True

def add_host_entry(host_manager: HostManager, host_address: str, hostname: str) -> None:
    app_name = os.environ.get('APP_NAME', 'web-server')
    header_comment = f"# START Added by {app_name} hosts"
    footer_comment = f"# END {app_name} hosts"
    entry = f"{host_address} {hostname}"

    print("Reading hosts file...")
    lines = host_manager.read_hosts()
    if lines is None:
        return

    print(f"Found {len(lines)} lines in hosts file.")

    header_index = find_header(lines, header_comment)
    footer_index = find_footer(lines, footer_comment)

    print(f"Header index: {header_index}, Footer index: {footer_index}")

    if manage_host_entry(lines, entry, header_comment, footer_comment, header_index, footer_index):
        print("Writing to hosts file...")
        host_manager.write_hosts(lines)
        print(f"Added entry: {entry}")

def get_host_manager(custom_path: str | None = None) -> HostManager:
    if custom_path:
        return CustomHostManager(custom_path)
    
    system = platform.system()
    if system == "Windows":
        return WindowsHostManager()
    elif system in ("Linux", "Darwin"):  # Darwin is macOS
        return UnixHostManager()
    else:
        raise ValueError(f"Unsupported operating system: {system}")

if __name__ == "__main__":
    print("Starting add-host script...")
    parser = argparse.ArgumentParser(description="Add a host entry to the hosts file.")
    parser.add_argument("hostname", help="The hostname to add (e.g., domain from domainModel)")
    parser.add_argument("--host_address", default="127.0.0.1", help="The host address (default: 127.0.0.1)")
    parser.add_argument("--custom_path", help="Custom path to the hosts file")
    args = parser.parse_args()

    try:
        host_manager = get_host_manager(args.custom_path)
        add_host_entry(host_manager, args.host_address, args.hostname)
    except ValueError as e:
        print(e)
    print("Script finished.")
    sys.exit(0)