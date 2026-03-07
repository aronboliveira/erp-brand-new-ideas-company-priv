import json
import pandas as pd
import os
import platform
import re
from datetime import datetime
from colorama import Fore, init
from typing import Any, Tuple
init(autoreset=True)
DepTuple = Tuple[str, str, str, str, str]
composer_deps: list[DepTuple] = []
npm_deps: list[DepTuple] = []
def extract_file_deps(file: str, _case: str) -> None:
    try:
        rel = os.path.relpath(file)
        with open(file, 'r', encoding='utf-8') as f:
            content = json.load(f)

        def add_deps(c: dict[str, Any], prop: str, group: str = 'general') -> None:
            for d, v in c.get(prop, {}).items():
                loaded_vn = re.sub(r'[^0-9]', '', v)
                found = None
                print(Fore.GREEN + f'Got {d} as a dependency')
                if _case == 'composer':
                    for i_c in composer_deps:
                        print(Fore.WHITE + f'Comparing to {d}...')
                        if i_c[2] == d:
                            found = i_c
                            break
                elif _case == 'npm':
                    for i_n in npm_deps:
                        if i_n[2] == d:
                            found = i_n
                            break
                else:
                    raise ValueError('Case does not exist in dependency check switch.')

                def case_append(item: DepTuple) -> None:
                    if _case == 'npm':
                        npm_deps.append(item)
                    elif _case == 'composer':
                        composer_deps.append(item)
                if found:
                    def case_remove() -> None:
                        if _case == 'npm':
                            npm_deps.remove(found)
                        elif _case == 'composer':
                            composer_deps.remove(found)
                    print(Fore.WHITE + f'{found[2]} already exists listed. Comparing versions...')
                    existing_v = found[3]
                    if (not existing_v and loaded_vn) or (existing_v == '*' and loaded_vn):
                        case_remove()
                        case_append((_case, group, d, v, rel))
                    else:
                        existing_vn = re.sub(r'[^0-9]', '', existing_v) if len(found[2]) > 3 else ''
                        if not existing_vn or float(existing_vn) < float(loaded_vn):
                            case_remove()
                            case_append((_case, group, d, v, rel))
                else:
                    case_append((_case, group, d, v, rel))
        if _case == 'npm':
            if 'dependencies' in content:
                add_deps(content, prop='dependencies')
            if 'devDependencies' in content:
                add_deps(content, prop='devDependencies', group='dev')
        elif _case == 'composer':
            if 'require' in content:
                add_deps(content, prop='require')
            if 'require-dev' in content:
                add_deps(content, prop='require-dev', group='dev')
        else:
            raise ValueError('Invalid given case.')
    except FileNotFoundError as e:
        print(f'The passed file {file} could not be recognized as a file: {e}')
    except ValueError as e:
        if not isinstance(file, str) or not isinstance(_case, str):
            print(Fore.RED + f"""Unexpected argument type given:
                                Type of file path: {type(file)}
                                Type of case: {type(_case)}""")
        elif _case not in ('npm', 'composer'):
            print(Fore.RED + f'The passed case for evaluation is not valid.\nObtained value: {_case}')
        else:
            print(Fore.RED + f'There has been an undefined error while working with a function value: {e}')
    except PermissionError:
        print(Fore.RED + f'Permission for processing {file} denied by {os.name} on {platform.platform()}')
    except Exception as e:
        print(Fore.RED + f'As unknown error has occurred: {e}')
def extract_all_deps() -> bool:
    print(Fore.YELLOW + 'Running extraction of dependencies...')
    try:
        for r, ds, fs in os.walk(os.path.dirname(os.path.abspath(__file__))):
            for f in fs:
                full_path = os.path.join(r, f)
                if f == 'composer.json' or f == 'package.json':
                    print(Fore.BLUE + f'Extracting dependencies from {full_path}...')
                    if f == 'composer.json':
                        extract_file_deps(full_path, 'composer')
                    elif f == 'package.json':
                        extract_file_deps(full_path, 'npm')
        name = f'extracted_dependencies_{datetime.now().strftime("%Y%m%d_%H%M%S")}.xlsx'
        all_deps = composer_deps + npm_deps
        if all_deps:
            pd.DataFrame(all_deps, columns=('Case', 'Group', 'Dependency', 'Version', 'Related Path')).to_excel(name, index=False)
            print(Fore.GREEN + f'Successfully extracted and tabled current directory dependencies.\nYou can find it in {name}')
        else:
            print(Fore.YELLOW + 'No dependencies found in the working directory.')
        return True
    except PermissionError:
        print(Fore.RED + f'Processing of {os.getcwd()} denied by {os.name} on {platform.platform()}')
        return False
    except Exception as e:
        print(Fore.RED + f'An unknown error has stopped the execution: {e}')
        return False


if __name__ == '__main__':
    extract_all_deps()
